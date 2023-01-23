<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Insightly\Models\InsightlyMappingModel;
use App\Insightly\Resources\ResourceType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateProjects extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file';

    public function handle(): int
    {
        Event::forget(IntegrationCreated::class);

        CauserResolver::setCauser(UserModel::createSystemUser());

        // Read the projects from CSV file
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

            $integrationModel = new IntegrationModel([
                'id' => $integrationId->toString(),
                'name' => $name,
                'description' => $description,
                'type' => IntegrationType::SearchApi,
                'status' => $status,
                'subscription_id' => 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26',
            ]);
            $integrationModel->save();

            if ($couponCode !== 'NULL' && $couponCode !== 'import') {
                $couponModel = CouponModel::query()->where('code', '=', $couponCode)->first();

                if ($couponModel === null) {
                    $this->warn('Coupon with code : ' . $couponCode . ' not found.');
                } else {
                    $couponModel->update([
                        'is_distributed' => true,
                        'integration_id' => $integrationId->toString(),
                    ]);
                }
            }

            if ($opportunityId !== 'NULL') {
                $opportunityMapping = new InsightlyMappingModel([
                    'id' => $integrationId->toString(),
                    'resource_type' => ResourceType::Opportunity,
                    'insightly_id' => $opportunityId
                ]);
                $opportunityMapping->save();
            }

            if ($projectId !== 'NULL') {
                $projectMapping = new InsightlyMappingModel([
                    'id' => $integrationId->toString(),
                    'resource_type' => ResourceType::Project,
                    'insightly_id' => $projectId
                ]);
                $projectMapping->save();
            }
        }

        return 0;
    }
}
