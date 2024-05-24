<?php

declare(strict_types=1);

namespace App\Keycloak\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Config;
use App\Keycloak\Jobs\MissingClientsDetected;
use App\Keycloak\RealmCollection;
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

    public function handleCreateClients(IntegrationCreated $event): void
    {
        $this->handle($event, $this->config->realms);
    }

    public function handleCreatingMissingClients(MissingClientsDetected $event): void
    {
        $missingRealms = $this->keycloakClientRepository->getMissingRealmsByIntegrationId($event->id);

        if (count($missingRealms) === 0) {
            $this->logger->info($event->id . ' - already has all Keycloak clients');
            return;
        }

        $this->handle($event, $missingRealms);
    }

    private function handle(IntegrationCreated|MissingClientsDetected $event, RealmCollection $realms): void
    {
        $clients = $this->createClientForRealms->createInKeycloak(
            $this->integrationRepository->getById($event->id),
            $realms
        );

        $this->keycloakClientRepository->create(...$clients);

        foreach ($clients as $client) {
            $this->logger->info('Keycloak client created', [
                'integration_id' => $event->id->toString(),
                'realm' => $client->realm->internalName,
            ]);
        }
    }

    public function failed(IntegrationCreated $integrationCreated, Throwable $throwable): void
    {
        $this->logger->error('Failed to create Keycloak clients', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
