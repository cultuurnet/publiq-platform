<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Keycloak\Models\KeycloakClientModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

final class MatchUdbOrganizerWithKeycloakClientIds extends Command
{
    protected $signature = 'udb-organizer:match-udb-organizer-with-client-id';

    protected $description = 'Migrate UdbOrganizers with no client_id to use Production Keycloak client';

    public function handle(): int
    {
        $udbOrganizersWithoutClient = UdbOrganizerModel::query()
            ->whereNull('client_id')
            ->get();

        if ($udbOrganizersWithoutClient->isEmpty()) {
            $this->info('No UdbOrganizers with null client_id found.');
            return self::SUCCESS;
        }

        if (!$this->confirm(sprintf('Are you sure you want to migrate %d UdbOrganizers? A backup could be smart. ;-)', $udbOrganizersWithoutClient->count()))) {
            return self::SUCCESS;
        }

        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        $progressBar = $this->output->createProgressBar($udbOrganizersWithoutClient->count());
        $progressBar->start();

        foreach ($udbOrganizersWithoutClient as $udbOrganizer) {
            try {
                $keycloakClient = KeycloakClientModel::query()
                    ->where('integration_id', $udbOrganizer->integration_id)
                    ->where('realm', Environment::Production->value)
                    ->firstOrFail();

                // Use query builder to bypass model events and activity logging
                UdbOrganizerModel::query()
                    ->where('id', $udbOrganizer->id)
                    ->update(['client_id' => $keycloakClient->id]);

                $successCount++;
            } catch (ModelNotFoundException) {
                $failureCount++;
                $this->error(sprintf(
                    'ERROR: No Production Keycloak client found for integration %s (UdbOrganizer: %s)',
                    $udbOrganizer->integration_id,
                    $udbOrganizer->id
                ));
            } catch (Throwable $e) {
                $failureCount++;
                $this->error(sprintf(
                    'ERROR: Failed to migrate UdbOrganizer %s: %s',
                    $udbOrganizer->id,
                    $e->getMessage()
                ));
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);

        if ($successCount > 0) {
            $this->info(sprintf('Successfully migrated %d UdbOrganizer(s)', $successCount));
        }
        if ($failureCount > 0) {
            $this->error(sprintf('Failed to migrate %d UdbOrganizer(s)', $failureCount));
        }

        return $failureCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
