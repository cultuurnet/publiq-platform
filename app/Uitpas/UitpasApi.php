<?php

declare(strict_types=1);

namespace App\Uitpas;

use App\Json;
use App\Keycloak\Client\HttpClient;
use App\Keycloak\Realm;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

final readonly class UitpasApi implements UitpasApiInterface
{
    public function __construct(
        private HttpClient $client,
        private LoggerInterface $logger,
    ) {
    }

    public function addPermissions(Realm $realm, string $organizerId, string $clientId): void
    {
        $request = new Request('PUT', 'permissions/' . $clientId, [
            'Accept' => 'application/problem+json',
            'Content-Type' => 'application/json',
        ], Json::encode($this->withBody($organizerId)));

        try {
            $response = $this->client->sendWithBearer(
                $request,
                $realm
            );
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, error %s', $organizerId, $clientId, $e->getMessage()));
            return;
        }

        if ($response->getStatusCode() !== 204) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, status code %s', $organizerId, $clientId, $response->getStatusCode()));
            return;
        }

        $this->logger->info(sprintf('Gave %s permission to uitpas organisation %s', $organizerId, $clientId));
    }

    private function withBody(string $organizerId): array
    {
        return [
            [
                'organizer' => [
                    'id' => $organizerId,
                ],
                'permissionDetails' => array_map(
                    static fn ($id) => ['id' => $id],
                    $this->basicPermissions()
                ),
            ],
        ];
    }

    private function basicPermissions(): array
    {
        return [
            'TARIFFS_READ',
            'TICKETSALES_REGISTER',
            'PASSES_READ',
            'PASSES_INSZNUMBERS_READ',
            'PASSES_CHIPNUMBERS_READ',
        ];
    }

    /** @return string[] */
    public function fetchPermissions(Realm $realm, Client $keycloakClient, string $organizerId): array
    {
        $myRequest = new Request('GET', 'permissions/' . $keycloakClient->clientId);

        $response = $this->client->sendWithBearer(
            $myRequest,
            $realm
        );

        /** @var array<int, array{organizer: array{id: string}, permissionDetails: array<int, array{label: array{nl: string}}>}> $json */
        $json = Json::decodeAssociatively($response->getBody()->getContents());

        return collect($json)
            ->filter(function (array $item) use ($organizerId): bool {
                return $item['organizer']['id'] === $organizerId;
            })
            ->flatMap(function (array $item): Collection {
                /** @var array<int, array{label: array{nl: string}}> $details */
                $details = $item['permissionDetails'];
                return collect($details)->pluck('label.nl');
            })
            ->sort()
            ->values()
            ->toArray();
    }
}
