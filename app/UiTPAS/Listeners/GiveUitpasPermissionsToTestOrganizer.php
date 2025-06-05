<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Realm;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class GiveUitpasPermissionsToTestOrganizer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTPASApiInterface $uitpasApi,
    ) {
    }

    public function handle(IntegrationCreated|IntegrationUpdated $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $keycloakClient = $integration->getKeycloakClientByEnv(Environment::Testing);

        $this->uitpasApi->addPermissions(
            Realm::getUitIdTestRealm(),
            (string)config(UiTPASConfig::TEST_ORGANISATION->value),
            $keycloakClient->clientId,
        );
    }
}
