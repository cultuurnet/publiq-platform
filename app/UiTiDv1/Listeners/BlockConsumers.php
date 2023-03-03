<?php

declare(strict_types=1);

namespace App\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class BlockConsumers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationBlocked $integrationBlocked): void
    {
        $consumers = $this->consumerRepository->getByIntegrationId($integrationBlocked->id);

        $this->clusterSDK->blockConsumers(...$consumers);

        $this->logger->info(
            'UiTiD v1 consumer(s) blocked',
            [
                'domain' => 'uitid',
                'integration_id' => $integrationBlocked->id->toString(),
            ]
        );
    }

    public function failed(IntegrationBlocked $integrationBlocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to block UiTiD v1 consumer(s)', [
            'integration_id' => $integrationBlocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
