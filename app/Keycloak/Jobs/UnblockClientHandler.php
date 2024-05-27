<?php

declare(strict_types=1);

namespace App\Keycloak\Jobs;

use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\ClientUnblocked;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final readonly class UnblockClientHandler implements ShouldQueue
{
    public function __construct(
        private ApiClient $apiClient,
        private KeycloakClientRepository $keycloakClientRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(
        UnblockClient $event
    ): void {
        try {
            $client = $this->keycloakClientRepository->getById($event->id);
            $this->apiClient->unblockClient($client);
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to unblock Keycloak client: ' . $e->getMessage(),
                [
                    'domain' => 'keycloak',
                    'id' => $event->id,
                ]
            );
            return;
        }

        $this->logger->info(
            'Keycloak client unblocked',
            [
                'domain' => 'keycloak',
                'id' => $event->id,
            ]
        );

        ClientUnblocked::dispatch($event->id);
    }
}
