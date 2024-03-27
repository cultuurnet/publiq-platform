<?php

declare(strict_types=1);

namespace App\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Events\ConsumerCreated;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1ClusterSDK;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateConsumers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UiTiDv1ClusterSDK $clusterSDK,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTiDv1ConsumerRepository $consumerRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $integration = $this->integrationRepository->getById($integrationCreated->id);
        $consumers = $this->clusterSDK->createConsumersForIntegration($integration);
        $this->consumerRepository->save(...$consumers);

        foreach ($consumers as $consumer) {
            Event::dispatch(new ConsumerCreated($consumer->id));

            $this->logger->info('UiTiD v1 consumer created', [
                'integration_id' => $integrationCreated->id->toString(),
                'tenant' => $consumer->environment->value,
                'consumer_id' => $consumer->consumerId,
                'consumer_key' => $consumer->consumerKey,
            ]);
        }
    }

    public function failed(IntegrationCreated $integrationCreated, Throwable $throwable): void
    {
        $this->logger->error('Failed to create UiTiD v1 consumer(s)', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
