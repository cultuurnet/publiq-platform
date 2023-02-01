<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateProjects extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file';

    public function handle(
        IntegrationRepository $integrationRepository,
        InsightlyMappingRepository $insightlyMappingRepository
    ): int {
        Model::unsetEventDispatcher();
        Event::forget(IntegrationActivatedWithCoupon::class);

        CauserResolver::setCauser(UserModel::createSystemUser());

        $projectsAsArray = $this->readCsvFile('projects.csv');

        $projectsCount = count($projectsAsArray);
        if ($projectsCount <= 0) {
            $this->warn('No projects to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $projectsCount . ' projects?')) {
            return 0;
        }

        foreach ($projectsAsArray as $projectAsArray) {
            if (!is_array($projectAsArray)) {
                continue;
            }

            $name = $projectAsArray[3];
            $description = $projectAsArray[16];
            $couponCode = $projectAsArray[8];
            $opportunityId = $projectAsArray[15];
            $projectId = $projectAsArray[14];

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
                $description,
                Uuid::fromString('b46745a1-feb5-45fd-8fa9-8e3ef25aac26'), // TODO: should be correct subscription plan
                $status,
                []
            );
            $integrationRepository->save($integration);

            if ($opportunityId === 'NULL' && $projectId === 'NULL') {
                $this->warn('Project with name ' . $name . ' has no Insightly id');
            }

            if ($couponCode !== 'NULL' && $couponCode !== 'import') {
                try {
                    $integrationRepository->activateWithCouponCode($integrationId, $couponCode);
                } catch (ModelNotFoundException) {
                    $this->warn('Coupon with code ' . $couponCode . ' not found.');
                }
            }

            if ($opportunityId !== 'NULL') {
                $opportunityMapping = new InsightlyMapping(
                    $integrationId,
                    (int) $opportunityId,
                    ResourceType::Opportunity
                );
                $insightlyMappingRepository->save($opportunityMapping);
            }

            if ($projectId !== 'NULL') {
                $projectMapping = new InsightlyMapping(
                    $integrationId,
                    (int)$projectId,
                    ResourceType::Project
                );
                $insightlyMappingRepository->save($projectMapping);
            }
        }

        return 0;
    }
}
