<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\ApiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class DisableClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly ApiClient $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationBlocked $integrationBlocked): void
    {
        $integration = $this->integrationRepository->getById($integrationBlocked->id);
        $keycloakClients = $this->keycloakClientRepository->getByIntegrationId($integrationBlocked->id);

        foreach ($keycloakClients as $keycloakClient) {
            try {
                $this->client->disableClient($keycloakClient);

                $this->logger->info('Keycloak client disabled', [
                    'integration_id' => $integration->id->toString(),
                    'client_id' => $keycloakClient->id->toString(),
                    'realm' => $keycloakClient->realm->internalName,
                ]);
            } catch (KeyCloakApiFailed $e) {
                $this->failed($integrationBlocked, $e);
            }
        }
    }

    public function failed(IntegrationBlocked $integrationBlocked, Throwable $throwable): void
    {
        $this->logger->error('Failed to disable Keycloak client(s)', [
            'integration_id' => $integrationBlocked->id->toString(),
            'exception' => $throwable,
        ]);
    }
}