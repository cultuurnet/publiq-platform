<?php

declare(strict_types=1);

namespace App\UiTiDv1\Jobs;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Events\ConsumerUnblocked;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1SDKException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;

final class UnblockConsumerHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(UnblockConsumer $event): void
    {
        try {
            $uiTiDv1Consumer = $this->consumerRepository->getById($event->id);
            $integration = $this->integrationRepository->getById($uiTiDv1Consumer->integrationId);
            $this->clusterSDK->unblockConsumers($integration, $uiTiDv1Consumer);
        } catch (ModelNotFoundException|UiTiDv1SDKException $e) {
            $this->logger->error(
                'Failed to unblock UiTiD v1 client: ' . $e->getMessage(),
                [
                    'domain' => 'uitid',
                    'id' => $event->id,
                    'exception' => $e,
                ]
            );
            return;
        }

        $this->logger->info(
            'UiTiD v1 consumer unblocked',
            [
                'domain' => 'uitid',
                'id' => $event->id,
            ]
        );

        Event::dispatch(new ConsumerUnblocked($event->id));
    }
}
