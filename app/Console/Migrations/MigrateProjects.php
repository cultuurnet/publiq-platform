<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

final class MigrateProjects extends Command
{
    protected $signature = 'migrate:projects';

    protected $description = 'Migrate the projects provided in the projects.csv CSV file';

    public function handle(): int
    {
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

            $status = IntegrationStatus::Draft;
            if ($projectAsArray[7] == 'active') {
                $status = IntegrationStatus::Active;
            }
            if ($projectAsArray[7] == 'blocked') {
                $status = IntegrationStatus::Blocked;
            }
            if ($projectAsArray[7] == 'application_sent') {
                $status = IntegrationStatus::PendingApprovalIntegration;
            }
            if ($projectAsArray[7] == 'waiting_for_payment') {
                $status = IntegrationStatus::PendingApprovalPayment;
            }

            $this->info('Importing project ' . $name);

            $now = Carbon::now();
            IntegrationModel::query()->insert([
                'id' => Uuid::uuid4(),
                'name' => $name,
                'description' => $description,
                'type' => IntegrationType::SearchApi,
                'status' => $status,
                'subscription_id' => 'b46745a1-feb5-45fd-8fa9-8e3ef25aac26',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return 0;
    }

    private function readCsvFile(string $csvFile): array
    {
        $rows = [];
        $fileHandle = fopen($csvFile, 'rb');

        while (!feof($fileHandle)) {
            $rows[] = fgetcsv($fileHandle);
        }
        fclose($fileHandle);

        return $rows;
    }
}
