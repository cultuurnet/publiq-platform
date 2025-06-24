<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\UdbUuid;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class AddUiTPASPermissionsToOrganizerForIntegration implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly KeycloakClientRepository $keycloakClientRepository,
        private readonly UiTPASApiInterface $uitpasApi,
        private readonly ClientCredentialsContext $testContext
    ) {
    }

    public function handle(ClientCreated $event): void
    {
        $keycloakClient = $this->keycloakClientRepository->getById($event->id);
        $integration = $this->integrationRepository->getById($keycloakClient->integrationId);

        if ($keycloakClient->environment !== $this->testContext->environment) {
            return;
        }

        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $this->uitpasApi->addPermissions(
            $this->testContext,
            new UdbUuid(config(UiTPASConfig::TEST_ORGANISATION->value)),
            $keycloakClient->clientId,
        );
    }
}
