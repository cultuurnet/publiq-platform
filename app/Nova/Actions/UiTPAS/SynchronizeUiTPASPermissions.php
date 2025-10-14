<?php

declare(strict_types=1);

namespace App\Nova\Actions\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionModelCollection;
use Laravel\Nova\Fields\ActionFields;
use Psr\Log\LoggerInterface;

class SynchronizeUiTPASPermissions extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ClientCredentialsContext $testContext,
        private readonly UdbUuid $demoOrgId,
        private readonly ClientCredentialsContext $prodContext,
        private readonly UiTPASApiInterface $uitpasApi,
        private readonly LoggerInterface $logger
    ) {

    }

    public function handle(ActionFields $fields, ActionModelCollection $actionModelCollection): void
    {
        $errors = [];
        foreach ($actionModelCollection as $integrationModel) {
            if (!$integrationModel instanceof IntegrationModel) {
                continue;
            }

            if ($integrationModel->type !== IntegrationType::UiTPAS) {
                continue;
            }

            $integration = $integrationModel->toDomain();
            $keycloakClientTestId = $integration->getKeycloakClientByEnv($this->testContext->environment)->clientId;
            $keycloakClientProdId = $integration->getKeycloakClientByEnv($this->prodContext->environment)->clientId;

            $this->uitpasApi->addPermissions($this->testContext, $this->demoOrgId, $keycloakClientTestId);

            $this->logger->info(sprintf("Restoring UiTPAS permissions for integration %s", $integration->id));

            foreach ($integration->udbOrganizers() as $organizer) {
                if ($organizer->status !== UdbOrganizerStatus::Approved) {
                    $this->logger->info(sprintf("Skipping organizer %s because its status is %s", $organizer->organizerId, $organizer->status->value));
                    continue;
                }

                $success = $this->uitpasApi->addPermissions($this->prodContext, $organizer->organizerId, $keycloakClientProdId);

                if (!$success) {
                    $errors[] = $organizer->id;
                    $this->logger->error(sprintf("Failed to restore UiTPAS permissions for organizer %s and Keycloak client %s", $organizer->organizerId, $keycloakClientProdId));
                }
            }
        }

        if ($errors !== []) {
            Action::danger('Some permissions could not be restored: ' . implode(', ', $errors));
            return;
        }

        Action::message('UiTPAS permissions restored successfully.');
    }
}
