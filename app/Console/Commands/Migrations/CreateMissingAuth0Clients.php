<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Auth0TenantNotConfigured;
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
        $migratedIntegrations = $this->getIntegrationsWithMissingAuth0Clients();

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

            $this->createMissingAuth0ClientsForIntegration($migratedIntegration);

            $this->info($migratedIntegration->id . ' - Finished creating Auth0 Clients');
            $this->info('---');
        }

        return 1;
    }

    private function createMissingAuth0ClientsForIntegration(Integration $integration): void
    {
        $auth0Clients = $this->auth0ClientRepository->getByIntegrationId($integration->id);

        if (count($auth0Clients) === count(Auth0Tenant::cases())) {
            $this->warn($integration->id . ' - already has all Auth0 clients');
            return;
        }

        $existingTenants = array_map(
            static fn (Auth0Client $auth0Client) => $auth0Client->tenant,
            $auth0Clients
        );
        $missingTenants = array_udiff(
            Auth0Tenant::cases(),
            $existingTenants,
            fn (Auth0Tenant $t1, Auth0Tenant $t2) => strcmp($t1->value, $t2->value)
        );

        foreach ($missingTenants as $missingTenant) {
            try {
                $auth0Client = $this->auth0ClusterSDK->createClientsForIntegrationOnAuth0Tenant($integration, $missingTenant);
                $this->auth0ClientRepository->save($auth0Client);

                $this->info($integration->id . ' - created Auth0 client on ' . $missingTenant->value);
            } catch (Auth0TenantNotConfigured) {
                $this->warn($integration->id . ' - missing config for Auth0 tenant ' . $missingTenant->value);
            }
        }
    }

    /**
     * @return Collection<Integration>
     */
    private function getIntegrationsWithMissingAuth0Clients(): Collection
    {
        $migratedIntegrationModels = IntegrationModel::query()->get();
        $migratedIntegrations = new Collection();

        foreach ($migratedIntegrationModels as $migratedIntegrationModel) {
            $migratedIntegrations->add($migratedIntegrationModel->toDomain());
        }

        return $migratedIntegrations;
    }
}
