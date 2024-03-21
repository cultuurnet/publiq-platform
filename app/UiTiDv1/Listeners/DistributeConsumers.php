<?php

declare(strict_types=1);

namespace App\UiTiDv1\Listeners;

use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTiDv1\Repositories\UiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class DistributeConsumers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTiDv1ConsumerRepository $uiTiDv1ConsumerRepository
    ) {
    }

    public function handle(IntegrationActivated $integrationActivated): void
    {
        $integration = $this->integrationRepository->getById($integrationActivated->id);
        $consumers = array_filter($integration->uiTiDv1Consumers(), fn (UiTiDv1Consumer $consumer) => $consumer->environment === UiTiDv1Environment::Production);

        $this->uiTiDv1ConsumerRepository->distribute(...$consumers);
    }
}
