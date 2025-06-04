<?php

declare(strict_types=1);

namespace App\Uitpas\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Realm;
use App\Uitpas\UitpasApiInterface;
use App\Uitpas\UitpasConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class GiveUitpasPermissionsToTestOrganizer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly UitpasApiInterface $uitpasApi,
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
            (string)config(UitpasConfig::TEST_ORGANISATION->value),
            $keycloakClient->clientId,
        );
    }
}
