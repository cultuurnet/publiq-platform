<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactKeyVisibilityRepository;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use App\UiTiDv1\UiTiDv1EnvironmentSDK;
use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SimpleXMLElement;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateProjects extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file (database/project-aanvraag/projects.csv)';

    private ClientInterface $oauthClientTest;
    private ClientInterface $oauthClientProduction;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly UiTiDv1ConsumerRepository $uiTiDv1ConsumerRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly ContactKeyVisibilityRepository $contactKeyVisibilityRepository,
        private readonly InsightlyClient $insightlyClient
    ) {
        parent::__construct();

        $this->oauthClientTest = UiTiDv1EnvironmentSDK::createOAuth1HttpClient(
            config('uitidv1.environments.test.baseUrl'),
            config('uitidv1.environments.test.consumerKey'),
            config('uitidv1.environments.test.consumerSecret')
        );
        $this->oauthClientProduction = UiTiDv1EnvironmentSDK::createOAuth1HttpClient(
            config('uitidv1.environments.prod.baseUrl'),
            config('uitidv1.environments.prod.consumerKey'),
            config('uitidv1.environments.prod.consumerSecret')
        );
    }

    public function handle(): int
    {
        Model::unsetEventDispatcher();
        Event::forget(IntegrationActivated::class);

        CauserResolver::setCauser(UserModel::createSystemUser());

        $rows = $this->readCsvFile('database/project-aanvraag/projects_with_subscriptions.csv');
        $migrationProjects = array_map(fn (array $row) => new MigrationProject($row), array_filter($rows));

        $projectsCount = count($migrationProjects);
        if ($projectsCount <= 0) {
            $this->warn('No projects to import');
            return 0;
        }

        if (!$this->confirm('Did you migrate the coupons first?')) {
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $projectsCount . ' projects?')) {
            return 0;
        }

        foreach ($migrationProjects as $migrationProject) {
            $integrationId = Uuid::uuid4();

            $this->info($integrationId . ' - Started importing project ' . $migrationProject->name());

            $this->migrateIntegration($integrationId, $migrationProject);

            if ($migrationProject->coupon() !== null) {
                $this->migrateCoupon($integrationId, $migrationProject->coupon());
            }

            $this->migrateMappings(
                $integrationId,
                $migrationProject->insightlyOpportunityId(),
                $migrationProject->insightlyProjectId()
            );

            if ($migrationProject->userUiTiD() !== null) {
                $this->migrateContact($integrationId, $migrationProject->userUiTiD());
            }

            $this->migrateKeys($integrationId, $migrationProject);

            $this->info($integrationId . ' - Ended importing project ' . $migrationProject->name());
            $this->info('---');
        }

        return 0;
    }

    private function migrateIntegration(UuidInterface $integrationId, MigrationProject $migrationProject): void
    {
        $subscriptionId = $this->subscriptionRepository->getByIntegrationTypeAndCategory(
            $migrationProject->type(),
            SubscriptionCategory::from($migrationProject->subscriptionCategory())
        )->id;

        $integration = new Integration(
            $integrationId,
            $migrationProject->type(),
            $migrationProject->name(),
            $migrationProject->description() !== null ? $migrationProject->description() : '',
            $subscriptionId,
            $migrationProject->status(),
            IntegrationPartnerStatus::THIRD_PARTY,
        );
        $integration = $integration->withKeyVisibility(KeyVisibility::v1);
        $this->integrationRepository->save($integration);

        IntegrationModel::query()->where('id', '=', $integrationId)->update([
            'migrated_at' => Carbon::now(),
        ]);
    }

    private function migrateCoupon(UuidInterface $integrationId, string $couponCode): void
    {
        try {
            /** @var CouponModel $couponModel */
            $couponModel = CouponModel::query()
                ->where('code', '=', $couponCode)
                ->whereNull('integration_id')
                ->firstOrFail();
            $couponModel->useOnIntegration($integrationId);
        } catch (ModelNotFoundException) {
            $this->warn($integrationId . ' - Coupon with code ' . $couponCode . ' not found.');
            return;
        }
    }

    private function migrateMappings(UuidInterface $integrationId, ?int $opportunityId, ?int $projectId): void
    {
        if ($opportunityId === null && $projectId === null) {
            $this->warn($integrationId . ' - Project has no Insightly ids');
            return;
        }

        if ($opportunityId !== null) {
            try {
                $this->insightlyClient->opportunities()->get($opportunityId);

                $opportunityMapping = new InsightlyMapping(
                    $integrationId,
                    $opportunityId,
                    ResourceType::Opportunity
                );
                $this->insightlyMappingRepository->save($opportunityMapping);
            } catch (Exception) {
                $this->warn($integrationId . ' - Did not find opportunity with id ' . $opportunityId);
            }
        }

        if ($projectId !== null) {
            try {
                $this->insightlyClient->projects()->get($projectId);

                $projectMapping = new InsightlyMapping(
                    $integrationId,
                    $projectId,
                    ResourceType::Project
                );
                $this->insightlyMappingRepository->save($projectMapping);
            } catch (Exception) {
                $this->warn($integrationId . ' - Did not find project with id ' . $projectId);
            }
        }
    }

    private function migrateContact(UuidInterface $integrationId, string $contactId): void
    {
        $uitIdContact = $this->getContactFromUiTiD($integrationId, $contactId);
        $email = $uitIdContact?->email;
        if ($uitIdContact === null || $email === null) {
            $this->warn($contactId . ' - user not found inside UiTiD');
            return;
        }

        $insightlyContacts = $this->insightlyClient->contacts()->findByEmail($email);
        if ($insightlyContacts->isEmpty()) {
            $this->warn($contactId . ' - user with email ' . $email . ' not found inside Insightly');
            $this->contactRepository->save($uitIdContact);
            return;
        }

        $insightlyContact = $insightlyContacts->mostLinks();
        if (count($insightlyContacts) > 1) {
            $this->warn($contactId . ' - found multiple contacts with email ' . $email . ' used ' . $insightlyContact->insightlyId);
        }

        $contact = $this->getContactFromInInsightly($integrationId, $contactId, $insightlyContact->insightlyId);
        $this->contactRepository->save($contact);

        try {
            $this->contactKeyVisibilityRepository->save($contact->email, KeyVisibility::v1);
        } catch (UniqueConstraintViolationException) {
            $this->info($contactId . ' - email ' . $contact->email . ' already has a key visibility');
        }
    }

    private function migrateKeys(UuidInterface $integrationId, MigrationProject $migrationProject): void
    {
        if ($migrationProject->apiKeyProduction()) {
            $consumerProduction = $this->getConsumerFromUitId(
                $integrationId,
                $migrationProject->apiKeyProduction(),
                UiTiDv1Environment::Production
            );
            if ($consumerProduction !== null) {
                $this->uiTiDv1ConsumerRepository->save($consumerProduction);
            }
        }

        if ($migrationProject->apiKeyTest()) {
            $consumerTest = $this->getConsumerFromUitId(
                $integrationId,
                $migrationProject->apiKeyTest(),
                UiTiDv1Environment::Testing
            );
            if ($consumerTest !== null) {
                $this->uiTiDv1ConsumerRepository->save($consumerTest);
            }
        }
    }

    private function getContactFromUiTiD(UuidInterface $integrationId, string $uitId): ?Contact
    {
        $response = $this->oauthClientProduction->request(
            'GET',
            'user/search?userId=' . $uitId,
            ['http_errors' => false]
        );
        $status = $response->getStatusCode();

        if ($status !== 200) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        $xml = new SimpleXMLElement($response->getBody()->getContents());
        $totalAsArray = $xml->xpath('total');
        $total = (int) $totalAsArray[0] ?: 0;
        if ($total === 0) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        /** @var array $emailArray */
        $emailArray = $xml->xpath('//foaf:mbox');
        $email = (string) $emailArray[0] ?: null;
        /** @var array $nickArray */
        $nickArray = $xml->xpath('//foaf:nick');
        $nick = (string) $nickArray[0] ?: null;

        if ($email === null) {
            $this->warn($uitId . ' - has no mbox inside UiTiD');
            return null;
        }

        if ($nick === null) {
            $this->warn($uitId . ' - has no nick inside UiTiD');
            return null;
        }

        return new Contact(
            Uuid::fromString($uitId),
            $integrationId,
            $email,
            ContactType::Contributor,
            $nick,
            ''
        );
    }

    private function getContactFromInInsightly(UuidInterface $integrationId, string $uitId, int $insightlyId): Contact
    {
        $contactAsArray = $this->insightlyClient->contacts()->get($insightlyId);

        return new Contact(
            Uuid::fromString($uitId),
            $integrationId,
            $contactAsArray['EMAIL_ADDRESS'],
            ContactType::Contributor,
            $contactAsArray['FIRST_NAME'],
            $contactAsArray['LAST_NAME'] ?? ''
        );
    }

    private function getConsumerFromUitId(
        UuidInterface $integrationId,
        string $apiKey,
        UiTiDv1Environment $environment
    ): ?UiTiDv1Consumer {
        $oauthClient = $this->oauthClientTest;
        if ($environment === UiTiDv1Environment::Production) {
            $oauthClient = $this->oauthClientProduction;
        }

        $response = $oauthClient->request(
            'GET',
            'serviceconsumer/apikey/' . $apiKey,
            ['http_errors' => false]
        );

        $status = $response->getStatusCode();
        if ($status !== 200) {
            $this->warn($integrationId . ' - did not find UiTiD consumer with API key ' . $apiKey);
            return null;
        }

        $xml = new SimpleXMLElement($response->getBody()->getContents());
        $data = Json::decodeAssociatively(Json::encode($xml));

        $consumerId = (string) $data['id'];
        $consumerKey = $data['consumerKey'];
        $consumerSecret = $data['consumerSecret'];

        return new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            $consumerId,
            $consumerKey,
            $consumerSecret,
            $apiKey,
            $environment
        );
    }
}
