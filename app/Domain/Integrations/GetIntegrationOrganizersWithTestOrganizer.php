<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Exceptions\KeycloakClientNotFound;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use Illuminate\Support\Collection;

final readonly class GetIntegrationOrganizersWithTestOrganizer
{
    public function __construct(
        private SearchService $searchClient,
        private UiTPASApiInterface $UiTPASApi,
        private ClientCredentialsContext $testCredentialsContext,
        private ClientCredentialsContext $prodCredentialsContext,
    ) {
    }

    public function getAndEnrichOrganisations(Integration $integration): Collection
    {
        $organizerIds = collect($integration->udbOrganizers())->map(fn (UdbOrganizer $organizer) => $organizer->organizerId);
        $UiTPASOrganizers = $this->searchClient->findUiTPASOrganizers(...$organizerIds)->getMember()?->getItems();
        $keycloakClient = $this->getClientByEnv($integration, Environment::Production);

        $organizers = collect($UiTPASOrganizers)->map(function (SapiOrganizer $organizer) use ($keycloakClient) {
            $id = explode('/', $organizer->getId() ?? '');
            $id = $id[count($id) - 1];

            return [
                'id' => $id,
                'name' => $organizer->getName()?->getValues() ?? [],
                'status' => 'Live',
                'permissions' => $keycloakClient ? $this->getLabels($this->UiTPASApi->fetchPermissions(
                    $this->prodCredentialsContext,
                    new UdbUuid($id),
                    $keycloakClient->clientId
                )) : [],
            ];
        });

        $keycloakClient = $this->getClientByEnv($integration, Environment::Testing);
        $orgTestId = (string)config(UiTPASConfig::TEST_ORGANISATION->value);
        $organizers->push([
            'id' => $orgTestId,
            'name' => ['nl' => 'UiTPAS Organisatie (Regio Gent + Paspartoe)'],
            'status' => 'Test',
            'permissions' => $keycloakClient ? $this->getLabels($this->UiTPASApi->fetchPermissions(
                $this->testCredentialsContext,
                new UdbUuid($orgTestId),
                $keycloakClient->clientId
            )) : [],
        ]);

        return $organizers;
    }

    private function getClientByEnv(Integration $integration, Environment $environment): ?Client
    {
        try {
            return $integration->getKeycloakClientByEnv($environment);
        } catch (KeycloakClientNotFound) {
            // Handle exception, throw null
        }
        return null;
    }

    /** @return string[] */
    private function getLabels(?UiTPASPermission $permission): array
    {
        if ($permission === null) {
            return [];
        }

        $labels = [];

        foreach ($permission->permissionDetails as $detail) {
            $labels[] = [
                'id' => $detail->id,
                'label' => ucfirst($detail->label)
            ];
        }

        return $labels;
    }
}
