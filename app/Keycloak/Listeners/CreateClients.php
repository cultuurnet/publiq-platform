<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Config;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\CreateClientForRealms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly Config $config,
        private readonly CreateClientForRealms $createClientForRealms,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $clients = $this->createClientForRealms->createInKeycloak(
            $this->integrationRepository->getById($integrationCreated->id),
            $this->config->realms
        );

        $this->keycloakClientRepository->create(...$clients);

        foreach ($clients as $client) {
            $this->logger->info('Keycloak client created', [
                'integration_id' => $integrationCreated->id->toString(),
                'realm' => $client->realm->internalName,
            ]);
        }
    }

    public function failed(IntegrationCreated $integrationCreated, Throwable $throwable): void
    {
        $this->logger->error('Failed to create Keycloak client(s)', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
