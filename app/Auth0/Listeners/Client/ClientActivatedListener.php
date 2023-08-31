<?php

declare(strict_types=1);

namespace App\Auth0\Listeners\Client;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Events\ClientActivated;
use App\Auth0\Repositories\Auth0ClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class ClientActivatedListener implements ShouldQueue
{
    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(
        ClientActivated $event
    ): void {
        $oauth0Client = $this->auth0ClientRepository->getById($event->id);

        if ($oauth0Client === null) {
            return;
        }

        $this->clusterSDK->activateClients($oauth0Client);

        $this->logger->info(
            'Auth0 client activated',
            [
                'domain' => 'auth0',
                'id' => $event->id,
            ]
        );
    }
}
