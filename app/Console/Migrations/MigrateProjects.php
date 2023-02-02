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
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
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
        private readonly InsightlyMappingRepository $insightlyMappingRepository
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

            $integrationId = $this->migrateIntegration($projectAsArray);

            $this->migrateCoupon($integrationId, $projectAsArray[8]);

            $this->migrateMappings($integrationId, $projectAsArray[15], $projectAsArray[14]);

            $this->migrateContact($integrationId, $projectAsArray[1]);
        }

        return 0;
    }

    private function migrateIntegration(array $projectAsArray): UuidInterface
    {
        $name = $projectAsArray[3];

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

        $this->info('Importing project ' . $name);

        $integrationId = Uuid::uuid4();

        $integration = new Integration(
            $integrationId,
            IntegrationType::SearchApi, // TODO: should be determined from data
            $name,
            $projectAsArray[16],
            Uuid::fromString('b46745a1-feb5-45fd-8fa9-8e3ef25aac26'), // TODO: should be correct subscription plan
            $status,
            []
        );
        $this->integrationRepository->save($integration);

        IntegrationModel::query()->where('id', '=', $integrationId)->update([
            'migrated_at' => Carbon::now(),
        ]);

        return $integrationId;
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
            $this->warn('Coupon with code ' . $couponCode . ' not found.');
            return false;
        }

        return true;
    }

    private function migrateMappings(UuidInterface $integrationId, string $opportunityId, string $projectId): bool
    {
        if ($opportunityId === 'NULL' && $projectId === 'NULL') {
            $this->warn('Project with name has no Insightly ids');
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
            $this->warn('Project with id ' . $integrationId . ' has no linked user');
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
            $this->warn('Contact with id ' . $contactId . ' not found inside contacts table');
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
