<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Integrations\Exceptions\KeycloakClientNotFound;
use App\Keycloak\Realm;
use App\Search\Sapi3\SearchService;
use App\Uitpas\UitpasApiInterface;
use App\Uitpas\UitpasConfig;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use Illuminate\Support\Collection;

final readonly class GetIntegrationOrganizersWithTestOrganizer
{
    public function __construct(
        private SearchService $searchClient,
        private UitpasApiInterface $uitpasApi,
    ) {
    }

    public function getAndEnrichOrganisations(Integration $integration): Collection
    {
        $organizerIds = collect($integration->udbOrganizers())->map(fn (UdbOrganizer $organizer) => $organizer->organizerId);
        $uitpasOrganizers = $this->searchClient->findUiTPASOrganizers(...$organizerIds)->getMember()?->getItems();
        $keycloakClient = $this->getClientByEnv($integration, Environment::Production);

        $organizers = collect($uitpasOrganizers)->map(function (SapiOrganizer $organizer) use ($keycloakClient) {
            $id = explode('/', $organizer->getId() ?? '');
            $id = $id[count($id) - 1];

            return [
                'id' => $id,
                'name' => $organizer->getName()?->getValues() ?? [],
                'status' => 'Live',
                'permissions' => $keycloakClient ? $this->uitpasApi->fetchPermissions(Realm::getUitIdProdRealm(), $keycloakClient, $id) : [],
            ];
        });

        $keycloakClient = $this->getClientByEnv($integration, Environment::Testing);
        $orgTestId = (string)config(UitpasConfig::TEST_ORGANISATION->value);
        $organizers->push([
            'id' => $orgTestId,
            'name' => ['nl' => 'UiTPAS Organisatie (Regio Gent + Paspartoe)'],
            'status' => 'Test',
            'permissions' => $keycloakClient ? $this->uitpasApi->fetchPermissions(Realm::getUitIdTestRealm(), $keycloakClient, $orgTestId) : [],
        ]);

        return $organizers;
    }

    private function getClientByEnv(Integration $integration, Environment $environment): ?\App\Keycloak\Client
    {
        try {
            $keycloakClient = $integration->getKeycloakClientByEnv($environment);
        } catch (KeycloakClientNotFound) {
            $keycloakClient = null;
        }
        return $keycloakClient;
    }
}
