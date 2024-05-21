<?php

declare(strict_types=1);

namespace App\Keycloak\Jobs;

use App\Keycloak\Events\ClientDisabled;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\ApiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final readonly class DisableClientHandler implements ShouldQueue
{
    public function __construct(
        private ApiClient $apiClient,
        private KeycloakClientRepository $keycloakClientRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(
        DisableClient $event
    ): void {
        try {
            $client = $this->keycloakClientRepository->getById($event->id);
            $this->apiClient->disableClient($client);
        } catch (ModelNotFoundException $e) {
            $this->logger->error(
                'Failed to disabled Keycloak client: ' . $e->getMessage(),
                [
                    'domain' => 'keycloak',
                    'id' => $event->id,
                ]
            );
            return;
        }

        $this->logger->info(
            'Keycloak client disabled',
            [
                'domain' => 'keycloak',
                'id' => $event->id,
            ]
        );

        ClientDisabled::dispatch($event->id);
    }
}
