<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use App\UiTiDv1\UiTiDv1EnvironmentSDK;
use Database\Seeders\SubscriptionsSeeder;
use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        Event::forget(IntegrationActivatedWithCoupon::class);

        CauserResolver::setCauser(UserModel::createSystemUser());

        $projectsAsArray = $this->readCsvFile('database/project-aanvraag/projects.csv');

        $projectsCount = count($projectsAsArray);
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

        foreach ($projectsAsArray as $projectAsArray) {
            if (!is_array($projectAsArray)) {
                continue;
            }

            $integrationId = Uuid::uuid4();

            $this->info($integrationId . ' - Started importing project ' . $projectAsArray[3]);

            $this->migrateIntegration($integrationId, $projectAsArray);

            $this->migrateCoupon($integrationId, $projectAsArray[8]);

            $this->migrateMappings($integrationId, (int) $projectAsArray[15], (int) $projectAsArray[14]);

            $this->migrateContact($integrationId, $projectAsArray[1]);

            $this->migrateKeys($integrationId, $projectAsArray);

            $this->info($integrationId . ' - Ended importing project ' . $projectAsArray[3]);
            $this->info('---');
        }

        return 0;
    }

    private function migrateIntegration(UuidInterface $integrationId, array $projectAsArray): void
    {
        $status = IntegrationStatus::Draft;
        if ($projectAsArray[7] === 'active') {
            $status = IntegrationStatus::Active;
        }
        if ($projectAsArray[7] === 'blocked') {
            $status = IntegrationStatus::Blocked;
        }
        if ($projectAsArray[7] === 'application_sent') {
            $status = IntegrationStatus::PendingApprovalIntegration;
        }
        if ($projectAsArray[7] === 'waiting_for_payment') {
            $status = IntegrationStatus::PendingApprovalPayment;
        }

        $integrationType = IntegrationType::EntryApi;
        if ($projectAsArray[6] === (string) config('insightly.integration_types.widgets')) {
            $integrationType = IntegrationType::Widgets;
        }
        if ($projectAsArray[6] === (string) config('insightly.integration_types.search_api')) {
            $integrationType = IntegrationType::SearchApi;
        }

        $integration = new Integration(
            $integrationId,
            $integrationType,
            $projectAsArray[3],
            $projectAsArray[16] !== 'NULL' ? $projectAsArray[16] : '',
            Uuid::fromString(SubscriptionsSeeder::BASIC_PLAN),
            $status,
            []
        );
        $this->integrationRepository->save($integration);

        IntegrationModel::query()->where('id', '=', $integrationId)->update([
            'migrated_at' => Carbon::now(),
        ]);
    }

    private function migrateCoupon(UuidInterface $integrationId, string $couponCode): void
    {
        if ($couponCode === 'NULL') {
            return;
        }

        if ($couponCode === 'import') {
            return;
        }

        try {
            $this->integrationRepository->activateWithCouponCode($integrationId, $couponCode);
        } catch (ModelNotFoundException) {
            $this->warn($integrationId . ' - Coupon with code ' . $couponCode . ' not found.');
            return;
        }
     }

    private function migrateMappings(UuidInterface $integrationId, int $opportunityId, int $projectId): void
    {
        if ($opportunityId === 0 && $projectId === 0) {
            $this->warn($integrationId . ' - Project has no Insightly ids');
            return;
        }

        if ($opportunityId !== 0) {
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

        if ($projectId !==  0) {
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
        if ($contactId === 'NULL') {
            $this->warn($integrationId . ' - Project has no linked user');
            return;
        }

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
    }

    private function migrateKeys(UuidInterface $integrationId, array $projectAsArray): void
    {
        // Creating missing UiTiD consumers and missing Auth0 clients will be handled by other scripts.
        $consumerProduction = $this->getConsumerFromUitId(
            $integrationId,
            $projectAsArray[12],
            UiTiDv1Environment::Production
        );
        if ($consumerProduction !== null) {
            $this->uiTiDv1ConsumerRepository->save($consumerProduction);
        }

        $consumerTest = $this->getConsumerFromUitId(
            $integrationId,
            $projectAsArray[13],
            UiTiDv1Environment::Testing
        );
        if ($consumerTest !== null) {
            $this->uiTiDv1ConsumerRepository->save($consumerTest);
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
        if ($apiKey === 'NULL') {
            return null;
        }

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
            $integrationId,
            $consumerId,
            $consumerKey,
            $consumerSecret,
            $apiKey,
            $environment
        );
    }
}
