<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

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
        $integrations = $this->getIntegrationsWithMissingAuth0Clients();

        $integrationsCount = count($integrations);
        if ($integrationsCount <= 0) {
            $this->warn('No integrations found with missing Auth0 clients');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to create Auth0 clients for ' . $integrationsCount . ' integrations?')) {
            return 0;
        }

        foreach ($integrations as $integration) {
            $this->info($integration->id . ' - Started creating Auth0 Clients');

            $this->createMissingAuth0ClientsForIntegration($integration);

            $this->info($integration->id . ' - Finished creating Auth0 Clients');
            $this->info('---');
        }

        return 1;
    }

    private function createMissingAuth0ClientsForIntegration(Integration $integration): void
    {
        $missingTenants = $this->auth0ClientRepository->getMissingTenantsByIntegrationId($integration->id);

        if (count($missingTenants) === 0) {
            $this->warn($integration->id . ' - already has all Auth0 clients');
            return;
        }

        foreach ($missingTenants as $missingTenant) {
            try {
                $auth0Client = $this->auth0ClusterSDK->createClientForIntegrationOnAuth0Tenant($integration, $missingTenant);
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
        $integrationModels = IntegrationModel::query()->has(
            'auth0Clients',
            '<',
            count(Auth0Tenant::cases())
        )->get();

        $integrations = new Collection();

        foreach ($integrationModels as $integrationModel) {
            $integrations->add($integrationModel->toDomain());
        }

        return $integrations;
    }
}
