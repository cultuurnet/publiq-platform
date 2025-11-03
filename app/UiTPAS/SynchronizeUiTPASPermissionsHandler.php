<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\UiTPAS\Dto\SynchronizeUiTPASResult;
use Psr\Log\LoggerInterface;

final readonly class SynchronizeUiTPASPermissionsHandler
{
    public function __construct(
        private ClientCredentialsContext $testContext,
        private UdbUuid $demoOrgId,
        private ClientCredentialsContext $prodContext,
        private UiTPASApiInterface $uitpasApi,
        private LoggerInterface $logger
    ) {

    }

    public function handle(Integration $integration): SynchronizeUiTPASResult
    {
        $failedOrganizerIds = [];

        if ($integration->type !== IntegrationType::UiTPAS) {
            return new SynchronizeUiTPASResult(false, []);
        }

        $keycloakClientTestId = $integration->getKeycloakClientByEnv($this->testContext->environment)->clientId;
        $keycloakClientProdId = $integration->getKeycloakClientByEnv($this->prodContext->environment)->clientId;

        $this->uitpasApi->updatePermissions($this->testContext, $this->demoOrgId, $keycloakClientTestId);

        $this->logger->info(sprintf('Restoring UiTPAS permissions for integration %s', $integration->id));

        foreach ($integration->udbOrganizers() as $organizer) {
            if ($organizer->status !== UdbOrganizerStatus::Approved) {
                $this->logger->info(sprintf('Skipping organizer %s because its status is %s', $organizer->organizerId, $organizer->status->value));
                continue;
            }

            $success = $this->uitpasApi->updatePermissions($this->prodContext, $organizer->organizerId, $keycloakClientProdId);

            if (!$success) {
                $failedOrganizerIds[] = $organizer->organizerId;

                $this->logger->error(sprintf('Failed to restore UiTPAS permissions for organizer %s and Keycloak client %s', $organizer->organizerId, $keycloakClientProdId));
            }
        }

        if (count($failedOrganizerIds) > 0) {
            return new SynchronizeUiTPASResult(false, $failedOrganizerIds);
        }

        return new SynchronizeUiTPASResult(true, []);
    }
}
