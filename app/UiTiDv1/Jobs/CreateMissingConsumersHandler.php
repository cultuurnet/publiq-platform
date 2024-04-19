<?php

declare(strict_types=1);

namespace App\UiTiDv1\Jobs;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateMissingConsumersHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(CreateMissingConsumers $createMissingConsumers): void
    {
        $missingEnvironments = $this->consumerRepository->getMissingEnvironmentsByIntegrationId($createMissingConsumers->id);

        if (count($missingEnvironments) === 0) {
            return;
        }

        $integration = $this->integrationRepository->getById($createMissingConsumers->id);
        foreach ($missingEnvironments as $missingEnvironment) {
            $uitidv1Consumer = $this->clusterSDK->createConsumerForIntegrationOnEnvironment(
                $integration,
                $missingEnvironment
            );
            $this->consumerRepository->save($uitidv1Consumer);

            $this->logger->info($integration->id . ' - created UiTiD v1 Consumer on ' . $missingEnvironment->value);
        }
    }

    public function failed(CreateMissingConsumers $createMissingConsumers, Throwable $throwable): void
    {
        $this->logger->error('Failed to create missing UiTiD v1 consumer(s)', [
            'integration_id' => $createMissingConsumers->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
