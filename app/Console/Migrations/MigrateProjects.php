<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Models\InsightlyContact;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\UiTiDv1\UiTiDv1EnvironmentSDK;
use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateProjects extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file';

    private ClientInterface $oauthClient;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly InsightlyClient $insightlyClient
    ) {
        parent::__construct();

        $this->oauthClient = UiTiDv1EnvironmentSDK::createOAuth1HttpClient(
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

        $projectsAsArray = $this->readCsvFile('projects.csv');

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
        if ($projectAsArray[6] === '24743') {
            $integrationType = IntegrationType::Widgets;
        }
        if ($projectAsArray[6] === '28808') {
            $integrationType = IntegrationType::SearchApi;
        }

        $integration = new Integration(
            $integrationId,
            $integrationType,
            $projectAsArray[3],
            $projectAsArray[16] !== 'NULL' ? $projectAsArray[16] : '',
            Uuid::fromString('b46745a1-feb5-45fd-8fa9-8e3ef25aac26'), // TODO: should be correct subscription plan
            $status,
            []
        );
        $this->integrationRepository->save($integration);

        IntegrationModel::query()->where('id', '=', $integrationId)->update([
            'migrated_at' => Carbon::now(),
        ]);
    }

    private function migrateCoupon(UuidInterface $integrationId, string $couponCode): bool
    {
        if ($couponCode === 'NULL') {
            return false;
        }

        if ($couponCode === 'import') {
            return false;
        }

        try {
            $this->integrationRepository->activateWithCouponCode($integrationId, $couponCode);
        } catch (ModelNotFoundException) {
            $this->warn($integrationId . ' - Coupon with code ' . $couponCode . ' not found.');
            return false;
        }

        return true;
    }

    private function migrateMappings(UuidInterface $integrationId, int $opportunityId, int $projectId): bool
    {
        if ($opportunityId === 0 && $projectId === 0) {
            $this->warn($integrationId . ' - Project has no Insightly ids');
            return false;
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

        return true;
    }

    private function migrateContact(UuidInterface $integrationId, string $contactId): bool
    {
        if ($contactId === 'NULL') {
            $this->warn($integrationId . ' - Project has no linked user');
            return false;
        }

        $uitIdContact = $this->getContactFromUiTiD($integrationId, $contactId);
        $email = $uitIdContact?->email;
        if ($uitIdContact === null || $email === null) {
            $this->warn($contactId . ' - user not found inside UiTiD');
            return false;
        }

        $insightlyContacts = $this->insightlyClient->contacts()->findByEmail($email);
        if (count($insightlyContacts) === 0) {
            $this->warn($contactId . ' - user with email ' . $email . ' not found inside Insightly');
            $this->saveContact($uitIdContact);
            return true;
        }

        // TODO: Sort on contacts with most links
        /** @var InsightlyContact $insightlyContact */
        $insightlyContact = Arr::sort($insightlyContacts)[0];
        if (count($insightlyContacts) > 1) {
            $this->warn($contactId . ' - found multiple contacts with email ' . $email . ' used ' . $insightlyContact->insightlyId);
        }

        $contact = $this->getContactFromInInsightly($integrationId, $contactId, $insightlyContact->insightlyId);
        $this->saveContact($contact);

        return true;
    }

    private function saveContact(Contact $contact): void
    {
        $this->info($contact->id . ' - importing user with email ' . $contact->email);
        $this->contactRepository->save($contact);

        ContactModel::query()->where('id', '=', $contact->id->toString())->update([
            'migrated_at' => Carbon::now(),
        ]);
    }

    private function getContactFromUiTiD(UuidInterface $integrationId, string $uitId): ?Contact
    {
        $response = $this->oauthClient->request(
            'GET',
            'user/search?userId=' . $uitId,
            ['http_errors' => false]
        );
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($status !== 200) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        $xmlString = Str::of($body);

        $total = $xmlString->between('<total>', '</total>')->toInteger();
        if ($total === 0) {
            $this->warn($uitId . ' - did not find user with UiTiD ' . $uitId);
            return null;
        }

        if (!$xmlString->contains('<foaf:mbox>')) {
            $this->warn($uitId . ' - has no mbox inside UiTiD');
            return null;
        }

        if (!$xmlString->contains('<foaf:nick>')) {
            $this->warn($uitId . ' - has no nick inside UiTiD');
            return null;
        }

        $email = $xmlString->between('<foaf:mbox>', '</foaf:mbox>')->toString();
        $nick = $xmlString->between('<foaf:nick>', '</foaf:nick>')->toString();

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
}
