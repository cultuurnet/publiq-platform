<?php

declare(strict_types=1);

namespace App\Auth0\Jobs;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Events\ClientUnblocked;
use App\Auth0\Repositories\Auth0ClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final class UnblockClientHandler implements ShouldQueue
{
    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(
        UnblockClient $event
    ): void {
        try {
            $this->clusterSDK->activateClients($this->auth0ClientRepository->getById($event->id));
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to unblock Auth0 client: ' . $e->getMessage(),
                [
                    'domain' => 'auth0',
                    'id' => $event->id,
                ]
            );
            return;
        }
        $this->logger->info(
            'Auth0 client unblocked',
            [
                'domain' => 'auth0',
                'id' => $event->id,
            ]
        );

        ClientUnblocked::dispatch($event->id);
    }
}
