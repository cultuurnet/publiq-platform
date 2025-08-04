<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\UdbUuid;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class AddUiTPASPermissionsToOrganizerForIntegration implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly UiTPASApiInterface $UiTPASApi,
        private readonly ClientCredentialsContext $testContext,
        private readonly ClientCredentialsContext $prodContext,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handleCreateTestPermissions(ClientCreated $event): void
    {
        $keycloakClient = $this->keycloakClientRepository->getById($event->id);
        $integration = $this->integrationRepository->getById($keycloakClient->integrationId);

        if ($keycloakClient->environment !== $this->testContext->environment) {
            return;
        }

        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $this->UiTPASApi->addPermissions(
            $this->testContext,
            new UdbUuid(config(UiTPASConfig::TEST_ORGANISATION->value)),
            $keycloakClient->clientId,
        );
    }

    public function handleCreateProductionPermissions(UdbOrganizerApproved $event): void
    {
        $success = $this->UiTPASApi->addPermissions(
            $this->prodContext,
            $event->udbId,
            $this->integrationRepository
                ->getById($event->integrationId)
                ->getKeycloakClientByEnv($this->prodContext->environment)
                ->clientId
        );

        if (!$success) {
            $this->logger->critical(
                'Failed to add UiTPAS permissions for organizer - The UiTPAS API might be down',
                [
                    'organizer_id' => $event->udbId,
                    'integration_id' => $event->integrationId,
                ]
            );
        }
    }
}
