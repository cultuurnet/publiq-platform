<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
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
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateProjects extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly InsightlyClient $insightlyClient
    ) {
        parent::__construct();
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

        $integration = new Integration(
            $integrationId,
            IntegrationType::SearchApi, // TODO: should be determined from data
            $projectAsArray[3],
            $projectAsArray[16] !== 'NULL' ? $projectAsArray[16] : null,
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
            $this->warn($integrationId . - 'Project has no linked user');
            return false;
        }

        $this->call(
            'migrate:user',
            [
                'uitId' => $contactId,
                '--no-interaction' => true,
            ]
        );

        try {
            $contact = $this->contactRepository->getById(Uuid::fromString($contactId));
        } catch (ModelNotFoundException) {
            $this->warn($integrationId . ' - Contact with id ' . $contactId . ' not found inside contacts table');
            return false;
        }

        $this->contactRepository->save(new Contact(
            $contact->id,
            $integrationId,
            $contact->email,
            $contact->type,
            $contact->firstName,
            $contact->lastName
        ));

        return true;
    }
}
