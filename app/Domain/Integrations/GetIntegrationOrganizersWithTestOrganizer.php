<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Exceptions\KeycloakClientNotFound;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use CultuurNet\SearchV3\ValueObjects\Organizer as SapiOrganizer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final readonly class GetIntegrationOrganizersWithTestOrganizer
{
    public function __construct(
        private SearchService $prodSearchService,
        private UiTPASApiInterface $UiTPASApi,
        private ClientCredentialsContext $testCredentialsContext,
        private ClientCredentialsContext $prodCredentialsContext,
        private KeycloakClientRepository $keycloakClientRepository,
    ) {
    }

    public function getAndEnrichOrganisations(Integration $integration): Collection
    {
        $prodOrganizers = $testOrganizers = [];
        $keycloakClientCache = [];
        foreach ($integration->udbOrganizers() as $udbOrganizer) {
            //@todo this can be simplified once client id can no longer be null
            try {
                if ($udbOrganizer->clientId === null) {
                    $prodOrganizers[] = $udbOrganizer;
                    continue;
                }

                $keycloakClient = $this->keycloakClientRepository->getById($udbOrganizer->clientId);
                $keycloakClientCache[$udbOrganizer->clientId->toString()] = $keycloakClient;

                if ($keycloakClient->environment === Environment::Production) {
                    $prodOrganizers[] = $udbOrganizer;
                } else {
                    $testOrganizers[] = $udbOrganizer;
                }
            } catch (ModelNotFoundException) {
                $prodOrganizers[] = $udbOrganizer;
            }
        }

        $organizers = collect()
            ->merge($this->mapOrganizers($this->testSearchService, $this->testCredentialsContext, $testOrganizers, 'Test', $integration, $keycloakClientCache))
            ->merge($this->mapOrganizers($this->prodSearchService, $this->prodCredentialsContext, $prodOrganizers, 'Live', $integration, $keycloakClientCache));

        // Only add demo user if not already added from the database.
        $testOrgId = (string)config(UiTPASConfig::TEST_ORGANISATION->value);
        if (!$organizers->contains(fn (array $organizer) => $organizer['id'] === $testOrgId)) {
            $organizers = $organizers->merge($this->addTestOrganizer($integration));
        }

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

    /** @return array<int, array{id: string, label: string}> */
    private function getLabels(?UiTPASPermission $permission): array
    {
        if ($permission === null) {
            return [];
        }

        $labels = [];

        foreach ($permission->permissionDetails as $detail) {
            $labels[] = [
                'id' => $detail->id,
                'label' => ucfirst($detail->label),
            ];
        }

        return $labels;
    }

    public function addTestOrganizer(Integration $integration): Collection
    {
        $output = collect();
        $keycloakClient = $this->getClientByEnv($integration, Environment::Testing);
        $orgTestId = (string)config(UiTPASConfig::TEST_ORGANISATION->value);
        $output->push([
            'id' => $orgTestId,
            'name' => ['nl' => 'UiTPAS Organisatie (Regio Gent + Paspartoe)'],
            'status' => 'Test',
            'permissions' => $keycloakClient ? $this->getLabels($this->UiTPASApi->fetchPermissions(
                $this->testCredentialsContext,
                new UdbUuid($orgTestId),
                $keycloakClient->clientId
            )) : [],
        ]);

        return $output;
    }

    private function mapOrganizers(
        SearchService $searchService,
        ClientCredentialsContext $context,
        array $udbOrganizers,
        string $status,
        Integration $integration,
        array $keycloakClientCache
    ): Collection {
        $organizerIds = array_map(fn (UdbOrganizer $item) => $item->organizerId, $udbOrganizers);

        // Fetch organizers from UDB and index by organizer ID
        $UiTPASOrganizers = collect($searchService->findOrganizers(...$organizerIds)->getMember()?->getItems() ?? [])->keyBy(function (SapiOrganizer $organizer) {
            $id = explode('/', $organizer->getId() ?? '');
            return $id[count($id) - 1];
        });

        $organizers = collect();

        foreach ($udbOrganizers as $udbOrganizer) {
            if (!$udbOrganizer instanceof UdbOrganizer) {
                continue;
            }

            $organizer = $UiTPASOrganizers->get($udbOrganizer->organizerId->toString());

            if (!$organizer) {
                continue;
            }

            if ($udbOrganizer->clientId === null) {
                //@todo This if can be removed later if clientId is no longer nullable
                $keycloakClient = $this->getClientByEnv($integration, Environment::Production);
            } else {
                $keycloakClient = $keycloakClientCache[$udbOrganizer->clientId->toString()] ?? null;
            }

            $organizers->push([
                'id' => $udbOrganizer->organizerId->toString(),
                'name' => $organizer->getName()?->getValues() ?? [],
                'status' => $status,
                'permissions' => $keycloakClient ? $this->getLabels($this->UiTPASApi->fetchPermissions(
                    $context,
                    $udbOrganizer->organizerId,
                    $keycloakClient->clientId
                )) : [],
            ]);
        }

        return $organizers;
    }
}
