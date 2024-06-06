<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationDeleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class BlockClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationBlocked|IntegrationDeleted $event): void
    {
        $auth0Clients = $this->auth0ClientRepository->getByIntegrationId($event->id);

        $this->clusterSDK->blockClients(...$auth0Clients);

        $this->logger->info(
            'Auth0 client(s) blocked',
            [
                'domain' => 'auth0',
                'integration_id' => $event->id->toString(),
            ]
        );
    }

    public function failed(IntegrationBlocked|IntegrationDeleted $event, Throwable $throwable): void
    {
        $this->logger->error('Failed to block Auth0 client(s)', [
            'integration_id' => $event->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
