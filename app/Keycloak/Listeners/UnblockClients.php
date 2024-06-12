<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UnblockClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly ApiClient $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUnblocked $integrationUnblocked): void
    {
        $integration = $this->integrationRepository->getById($integrationUnblocked->id);
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($integrationUnblocked->id);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $this->client->unblockClient($keycloakClient);

                $this->logger->info('Keycloak client unblocked', [
                    'integration_id' => $integration->id->toString(),
                    'client_id' => $keycloakClient->id->toString(),
                    'environment' => $keycloakClient->environment->value,
                ]);
            } catch (KeyCloakApiFailed $e) {
                $this->failed($integrationUnblocked, $e);
            }
        }
    }

    public function failed(IntegrationUnblocked $integrationUnblocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to block Keycloak client(s)', [
            'integration_id' => $integrationUnblocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
