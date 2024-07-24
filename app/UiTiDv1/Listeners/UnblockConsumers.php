<?php

declare(strict_types=1);

namespace App\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UnblockConsumers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationUnblocked $integrationUnblocked): void
    {
        $consumers = $this->consumerRepository->getByIntegrationId($integrationUnblocked->id);

        $integration = $this->integrationRepository->getById($integrationUnblocked->id);
        $this->clusterSDK->unblockConsumers($integration, ... $consumers);

        $this->logger->info(
            'UiTiD v1 consumer(s) unblocked',
            [
                'domain' => 'uitid',
                'integration_id' => $integrationUnblocked->id->toString(),
            ]
        );
    }

    public function failed(IntegrationUnblocked $integrationUnblocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to unblock UiTiD v1 consumer(s)', [
            'integration_id' => $integrationUnblocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
