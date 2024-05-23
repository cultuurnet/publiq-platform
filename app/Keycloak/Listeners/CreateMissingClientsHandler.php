<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Jobs\CreateMissingClients;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\CreateClientForRealms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CreateMissingClientsHandler implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly LoggerInterface $logger,
        private readonly CreateClientForRealms $createClientForRealms
    ) {
    }

    public function handle(CreateMissingClients $event): void
    {
        $missingRealms = $this->keycloakClientRepository->getMissingRealmsByIntegrationId($event->id);

        if (count($missingRealms) === 0) {
            $this->logger->info($event->id . ' - already has all Keycloak clients');
            return;
        }

        $clients = $this->createClientForRealms->createInKeycloak(
            $this->integrationRepository->getById($event->id),
            $missingRealms
        );

        $this->keycloakClientRepository->create(...$clients);

        foreach ($clients as $client) {
            $this->logger->info('Keycloak client created', [
                'integration_id' => $event->id->toString(),
                'client_id' => $client->id->toString(),
                'realm' => $client->realm->internalName,
            ]);
        }
    }

    public function failed(CreateMissingClients $event, Throwable $throwable): void
    {
        $this->logger->error('Failed to create missing Keycloak clients', [
            'integration_id' => $event->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
