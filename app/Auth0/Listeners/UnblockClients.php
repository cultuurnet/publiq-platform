<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UnblockClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationUnblocked $integrationUnblocked): void
    {
        $auth0Clients = $this->auth0ClientRepository->getByIntegrationId($integrationUnblocked->id);

        $this->clusterSDK->unblockClients(...$auth0Clients);

        $this->logger->info(
            'Auth0 client(s) unblocked',
            [
                'domain' => 'auth0',
                'integration_id' => $integrationUnblocked->id->toString(),
            ]
        );
    }

    public function failed(IntegrationUnblocked $integrationUnblocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to block Auth0 client(s)', [
            'integration_id' => $integrationUnblocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
