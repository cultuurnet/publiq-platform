<?php

declare(strict_types=1);

namespace App\Keycloak\Jobs;

use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\ClientEnabled;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final readonly class EnableClientHandler implements ShouldQueue
{
    public function __construct(
        private ApiClient $apiClient,
        private KeycloakClientRepository $keycloakClientRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(
        EnableClient $event
    ): void {
        try {
            $client = $this->keycloakClientRepository->getById($event->id);
            $this->apiClient->enableClient($client);
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to enable Keycloak client: ' . $e->getMessage(),
                [
                    'domain' => 'keycloak',
                    'id' => $event->id,
                ]
            );
            return;
        }

        $this->logger->info(
            'Keycloak client enabled',
            [
                'domain' => 'keycloak',
                'id' => $event->id,
            ]
        );

        ClientEnabled::dispatch($event->id);
    }
}
