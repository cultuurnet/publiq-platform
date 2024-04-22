<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1Environment;
use App\UiTiDv1\UiTiDv1EnvironmentNotConfigured;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class CreateMissingUiTiDv1Consumers extends Command
{
    protected $signature = 'uitidv1:create-missing-uitidv1-consumers';

    protected $description = 'Create UiTiD v1 consumers(s) for integrations that are missing one or more';

    public function __construct(
        private readonly UiTiDv1ClusterSDK $uiTiDv1ClusterSDK,
        private readonly UiTiDv1ConsumerRepository $uiTiDv1ConsumerRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $integrations = $this->getIntegrationsWithMissingUiTiDv1Consumers();

        $integrationsCount = count($integrations);
        if ($integrationsCount <= 0) {
            $this->warn('No integrations with missing UiTiD v1 Consumers found');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to create UiTiD v1 Consumers for ' . $integrationsCount . ' integrations?')) {
            return 0;
        }

        foreach ($integrations as $integration) {
            $this->info($integration->id . ' - Started creating UiTiD v1 Consumers');

            $this->createMissingUiTiDConsumerForIntegration($integration);

            $this->info($integration->id . ' - Finished creating UiTiD v1 Consumers');
            $this->info('---');
        }

        return 1;
    }

    private function createMissingUiTiDConsumerForIntegration(Integration $integration): void
    {
        $missingEnvironments = $this->uiTiDv1ConsumerRepository->getMissingEnvironmentsByIntegrationId($integration->id);

        if (count($missingEnvironments) === 0) {
            $this->warn($integration->id . ' - already has all UiTiD v1 consumers');
        }

        foreach ($missingEnvironments as $missingEnvironment) {
            try {
                $uitidv1Consumer = $this->uiTiDv1ClusterSDK->createConsumerForIntegrationOnEnvironment(
                    $integration,
                    $missingEnvironment
                );
                $this->uiTiDv1ConsumerRepository->save($uitidv1Consumer);

                $this->info($integration->id . ' - created UiTiD v1 Consumer on ' . $missingEnvironment->value);
            } catch (UiTiDv1EnvironmentNotConfigured) {
                $this->warn($integration->id . ' - missing config for UiTiD v1 environment ' . $missingEnvironment->value);
            }
        }
    }

    /**
     * @return Collection<Integration>
     */
    private function getIntegrationsWithMissingUiTiDv1Consumers(): Collection
    {
        $integrationModels = IntegrationModel::query()->has(
            'uiTiDv1Consumers',
            '<',
            count(UiTiDv1Environment::cases())
        )->get();

        $integrations = new Collection();

        foreach ($integrationModels as $integrationModel) {
            $integrations->add($integrationModel->toDomain());
        }

        return $integrations;
    }
}
