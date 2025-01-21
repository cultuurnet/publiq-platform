<?php

declare(strict_types=1);

namespace App\Keycloak\Jobs;

use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\ClientBlocked;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final readonly class BlockClientHandler implements ShouldQueue
{
    public function __construct(
        private ApiClient $apiClient,
        private KeycloakClientRepository $keycloakClientRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(
        BlockClient $event
    ): void {
        try {
            $client = $this->keycloakClientRepository->getById($event->id);
            $this->apiClient->blockClient($client);
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to block Keycloak client: ' . $e->getMessage(),
                [
                    'domain' => 'keycloak',
                    'id' => $event->id,
                    'exception' => $e,
                ]
            );
            return;
        }

        $this->logger->info(
            'Keycloak client blocked',
            [
                'domain' => 'keycloak',
                'id' => $event->id,
            ]
        );

        ClientBlocked::dispatch($event->id);
    }
}
