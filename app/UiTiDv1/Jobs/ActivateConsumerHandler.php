<?php

declare(strict_types=1);

namespace App\UiTiDv1\Jobs;

use App\UiTiDv1\Events\ConsumerActivated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use App\UiTiDv1\UiTiDv1SDKException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;

final class ActivateConsumerHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(ActivateConsumer $event): void
    {
        try {
            $this->clusterSDK->activateConsumers($this->consumerRepository->getById($event->id));
        } catch (ModelNotFoundException|UiTiDv1SDKException $e) {
            $this->logger->error(
                'Failed to activate UiTiD v1 client: ' . $e->getMessage(),
                [
                    'domain' => 'uitid',
                    'id' => $event->id,
                ]
            );
            return;
        }

        $this->logger->info(
            'UiTiD v1 consumer activated',
            [
                'domain' => 'uitid',
                'id' => $event->id,
            ]
        );

        Event::dispatch(new ConsumerActivated($event->id));
    }
}
