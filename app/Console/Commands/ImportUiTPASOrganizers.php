<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Helper\CsvReader;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Helper\ProgressBar;

final class ImportUiTPASOrganizers extends Command
{
    protected $signature = 'import-uitpas-organizers {path=data/organizers-uitpas.csv : Path to the CSV file containing organizer data}';

    protected $description = 'Import UiTPAS organizers from a CSV file.';

    public function __construct(
        private readonly KeycloakClientRepository $keycloakClientRepository,
    ) {
        parent::__construct();
    }


    public function handle(): int
    {
        $path = $this->argument('path');

        // Make path relative to a base path if it doesn't start with /
        if (!str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        if (!File::exists($path)) {
            $this->error("CSV file not found: {$path}");
            return 1;
        }

        $this->info("Reading UiTPAS organizers from: {$path}");

        $csvData = (new CsvReader())->readCsvFile($path);

        if (empty($csvData)) {
            $this->info('No organizer data found in CSV file.');
            return 0;
        }

        // Calculate total number of organizers to process and sorting organizers per client
        $totalOrganizers = 0;
        $organizersByClient = [];
        foreach ($csvData as $row) {
            $organizerIds = $this->parseOrganizerIds($row['group_concat(b.actorid)'] ?? '');
            $totalOrganizers += count($organizerIds);
            $organizersByClient[$row['clientid']] = $organizerIds;
        }

        if (!$this->confirm(sprintf("Do you want to import %s organizers for %s integrations?", $totalOrganizers, count($csvData)))) {
            $this->info('Import cancelled.');
            return 0;
        }

        $progressBar = $this->startProgressBar($totalOrganizers);

        $processedCount = 0;
        foreach ($csvData as $row) {
            $clientId = $row['clientid'];
            try {
                $client = $this->keycloakClientRepository->getByClientId($clientId);
            } catch (ModelNotFoundException) {
                $this->error(sprintf('Client %s not found.', $clientId));
                continue;
            }

            foreach ($organizersByClient[$clientId] as $organizerId) {
                $processedCount++;

                // Create organizer without triggering model events to avoid sending emails
                UdbOrganizerModel::withoutEvents(static function () use ($client, $organizerId) {
                    UdbOrganizerModel::query()->create([
                        'id' => Uuid::uuid4()->toString(),
                        'integration_id' => $client->integrationId,
                        'organizer_id' => $organizerId,
                        'status' => UdbOrganizerStatus::Approved->value,
                    ]);
                });

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();

        $this->info(sprintf('Successfully imported %d organizers.', $processedCount));
        return self::SUCCESS;
    }

    private function parseOrganizerIds(string $organizerIdsString): array
    {
        if (empty($organizerIdsString)) {
            return [];
        }

        return array_unique(array_filter(
            array_map('trim', explode(',', $organizerIdsString)),
            fn ($id) => !empty($id)
        ));
    }

    private function startProgressBar(int $totalOrganizers): ProgressBar
    {
        $progressBar = $this->output->createProgressBar($totalOrganizers);
        $progressBar->setFormat('verbose');
        $progressBar->start();
        return $progressBar;
    }
}
