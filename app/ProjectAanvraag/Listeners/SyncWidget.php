<?php

declare(strict_types=1);

namespace App\ProjectAanvraag\Listeners;

use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Events\IntegrationUnblocked;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Exceptions\KeycloakClientNotFound;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Events\ClientsCreated;
use App\ProjectAanvraag\ProjectAanvraagClient;
use App\ProjectAanvraag\Requests\SyncWidgetRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class SyncWidget implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProjectAanvraagClient $projectAanvraagClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository,
        private readonly int $groupId,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handleIntegrationCreated(IntegrationCreated $integrationCreated): void
    {
        $this->handle($integrationCreated->id);
    }

    public function handleContactCreated(ContactCreated $contactCreated): void
    {
        $contact = $this->contactRepository->getById($contactCreated->id);
        $this->handle($contact->integrationId);
    }

    public function handleClientsCreated(ClientsCreated $clientsCreated): void
    {
        $this->handle($clientsCreated->id);
    }

    public function handleIntegrationActivated(IntegrationActivated $integrationActivated): void
    {
        $this->handle($integrationActivated->id);
    }

    public function handleIntegrationBlocked(IntegrationBlocked $integrationBlocked): void
    {
        $this->handle($integrationBlocked->id);
    }

    public function handleIntegrationUnblocked(IntegrationUnblocked $integrationUnblocked): void
    {
        $this->handle($integrationUnblocked->id);
    }

    public function handleIntegrationDeleted(IntegrationDeleted $integrationDeleted): void
    {
        $this->handle($integrationDeleted->id);
    }

    public function handleIntegrationUpdated(IntegrationUpdated $integrationUpdated): void
    {
        $this->handle($integrationUpdated->id);
    }

    private function handle(UuidInterface $integrationId): void
    {
        try {
            $integration = $this->integrationRepository->getById($integrationId);
        } catch (ModelNotFoundException) {
            $integration = $this->integrationRepository->getByIdWithTrashed($integrationId);
        }
        if ($integration->type !== IntegrationType::Widgets) {
            $this->logger->info(
                'Integration {integrationId} is not a widget integration, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $contacts = $this->contactRepository->getByIntegrationId($integration->id);
        if ($contacts->count() === 0) {
            $this->logger->info(
                'Integration {integrationId} has no contacts, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }
        $contributor = $contacts->firstWhere('type', ContactType::Contributor);
        if ($contributor === null) {
            $this->logger->info(
                'Integration {integrationId} has no contributor, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $userId = $this->userRepository->findUserIdByEmail($contributor->email);
        if ($userId === null) {
            $this->logger->info(
                'Integration {integrationId} Auth0 contact {$email} not found, skipping widget creation',
                [
                    'integrationId' => $integration->id->toString(),
                    'email' => $contributor->email,
                ]
            );
            return;
        }

        try {
            $testClient = $integration->getKeycloakClientByEnv(Environment::Testing);
        } catch (KeycloakClientNotFound) {
            $this->logger->info(
                'Integration {integrationId} has no Keycloak testing client, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }
        try {
            $liveClient = $integration->getKeycloakClientByEnv(Environment::Production);
        } catch (KeycloakClientNotFound) {
            $this->logger->info(
                'Integration {integrationId} has no Keycloak production client, skipping widget creation',
                ['integrationId' => $integration->id->toString()]
            );
            return;
        }

        $this->projectAanvraagClient->syncWidget(
            new SyncWidgetRequest(
                $integration->id,
                $userId,
                $integration->name,
                $integration->description,
                $integration->status,
                $this->groupId,
                $testClient->clientId,
                $liveClient->clientId
            )
        );
    }

    public function failed(
        IntegrationCreated|
        ContactCreated|
        ClientsCreated|
        IntegrationActivated|
        IntegrationBlocked|
        IntegrationUnblocked|
        IntegrationDeleted|
        IntegrationUpdated $event,
        Throwable $throwable
    ): void {
        $entity = match (get_class($event)) {
            IntegrationCreated::class,
            IntegrationActivated::class,
            IntegrationBlocked::class,
            IntegrationUnblocked::class,
            IntegrationDeleted::class,
            IntegrationUpdated::class,
            ClientsCreated::class => 'integration',
            ContactCreated::class => 'contact',
        };

        $this->logger->error('Failed to create widget', [
            "{$entity}_id" => $event->id->toString(),
            'exception' => $throwable,
        ]);
    }
}
