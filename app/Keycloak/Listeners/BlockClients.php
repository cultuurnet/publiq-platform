<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class BlockClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly ApiClient $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationBlocked|IntegrationDeleted $integrationBlocked): void
    {
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($integrationBlocked->id);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $this->client->blockClient($keycloakClient);

                $this->logger->info('Keycloak client blocked', [
                    'integration_id' => $integrationBlocked->id->toString(),
                    'client_id' => $keycloakClient->id->toString(),
                    'environment' => $keycloakClient->environment->value,
                ]);
            } catch (KeyCloakApiFailed $e) {
                $this->failed($integrationBlocked, $e);
            }
        }
    }

    public function failed(IntegrationBlocked|IntegrationDeleted $integrationBlocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to block Keycloak client(s)', [
            'integration_id' => $integrationBlocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
