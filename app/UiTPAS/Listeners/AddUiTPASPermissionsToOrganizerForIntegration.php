<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class AddUiTPASPermissionsToOrganizerForIntegration implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly UiTPASApiInterface $uitpasApi,
        private readonly ClientCredentialsContext $testContext
    ) {
    }

    public function handle(IntegrationCreated $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $keycloakClient = $integration->getKeycloakClientByEnv(Environment::Testing);

        $this->uitpasApi->addPermissions(
            $this->testContext,
            (string)config(UiTPASConfig::TEST_ORGANISATION->value),
            $keycloakClient->clientId,
        );
    }
}
