<?php

declare(strict_types=1);

namespace App\Auth0\Jobs;

use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Events\ClientBlocked;
use App\Auth0\Repositories\Auth0ClientRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;

final class BlockClientHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly Auth0ClusterSDK $clusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(BlockClient $event): void
    {
        try {
            $this->clusterSDK->blockClients($this->auth0ClientRepository->getById($event->id));
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to block Auth0 client: ' . $e->getMessage(),
                [
                    'domain' => 'auth0',
                    'id' => $event->id,
                ]
            );
            return;
        }

        $this->logger->info(
            'Auth0 client blocked',
            [
                'domain' => 'auth0',
                'id' => $event->id,
            ]
        );

        ClientBlocked::dispatch($event->id);
    }
}
