<?php

declare(strict_types=1);

namespace App\Notifications\Listeners;

use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class NotifyIntegrationChanged implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly Notifier $notifier,
        private readonly MessageBuilder $messageBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationCreated|IntegrationActivationRequested $integrationChanged): void
    {
        $integration = $this->integrationRepository->getById($integrationChanged->id);

        $this->notifier->postMessage($this->messageBuilder->toMessage($integration));
    }

    public function failed(
        IntegrationCreated|IntegrationActivationRequested $integrationChanged,
        Throwable $throwable
    ): void {
        $this->logger->error('Failed to notify about integration change)', [
            'integration_id' => $integrationChanged->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
