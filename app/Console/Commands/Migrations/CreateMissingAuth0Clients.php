<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class CreateMissingAuth0Clients extends Command
{
    protected $signature = 'auth0:create-missing-auth0-clients';

    protected $description = 'Create Auth0 client(s) for integrations that are missing one or more';

    public function __construct(
        private readonly Auth0ClusterSDK $auth0ClusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $migratedIntegrations = $this->getMigratedIntegrations();

        $migratedIntegrationsCount = count($migratedIntegrations);
        if ($migratedIntegrationsCount <= 0) {
            $this->warn('No migrated integrations found');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to create Auth0 clients for ' . $migratedIntegrationsCount . ' integrations?')) {
            return 0;
        }

        foreach ($migratedIntegrations as $migratedIntegration) {
            $this->info($migratedIntegration->id . ' - Started creating Auth0 Clients');

            $this->createAuth0ClientsForIntegration($migratedIntegration);

            $this->info($migratedIntegration->id . ' - Finished creating Auth0 Clients');
            $this->info('---');
        }

        return 1;
    }

    private function createAuth0ClientsForIntegration(Integration $integration): void
    {
        $auth0Clients = $this->auth0ClientRepository->getByIntegrationId($integration->id);

        if (count($auth0Clients) > 0) {
            $this->warn($integration->id . ' - already has Auth0 clients');
            return;
        }

        $auth0Clients = $this->auth0ClusterSDK->createClientsForIntegration($integration);
        $this->auth0ClientRepository->save(...$auth0Clients);
    }

    /**
     * @return Collection<Integration>
     */
    private function getMigratedIntegrations(): Collection
    {
        $migratedIntegrationModels = IntegrationModel::query()->whereNotNull('migrated_at')->get();
        $migratedIntegrations = new Collection();

        foreach ($migratedIntegrationModels as $migratedIntegrationModel) {
            $migratedIntegrations->add($migratedIntegrationModel->toDomain());
        }

        return $migratedIntegrations;
    }
}
