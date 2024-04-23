<?php

declare(strict_types=1);

namespace App\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UpdateConsumers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationUpdated $integrationUpdated): void
    {
        $integration = $this->integrationRepository->getById($integrationUpdated->id);
        $consumers = $this->consumerRepository->getByIntegrationId($integrationUpdated->id);

        $this->clusterSDK->updateConsumersForIntegration($integration, ...$consumers);

        $this->logger->info(
            'UiTiD v1 consumer(s) updated',
            [
                'domain' => 'uitid',
                'integration_id' => $integrationUpdated->id->toString(),
            ]
        );
    }

    public function failed(IntegrationUpdated $integrationUpdated, Throwable $throwable): void
    {
        $this->logger->error('Failed to update UiTiD v1 consumer(s)', [
            'integration_id' => $integrationUpdated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
