<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\TokenStrategy;
use App\Domain\Integrations\Environment;
use App\Json;
use App\Keycloak\Client;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\Realm;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final readonly class UiTPASApi implements UiTPASApiInterface
{
    public function __construct(
        private ClientInterface $client,
        private TokenStrategy $tokenStrategy,
        private LoggerInterface $logger,
        private string $testApiEndpoint,
        private string $prodApiEndpoint,
    ) {
    }

    public function addPermissions(ClientCredentialsContext $context, string $organizerId, string $clientId): void
    {
        $request = new Request('PUT', 'permissions/' . $clientId, [
            'Accept' => 'application/problem+json',
            'Content-Type' => 'application/json',
        ], Json::encode($this->withBody($organizerId)));

        try {
            $response = $this->sendWithBearer(
                $request,
                $context
            );
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, error %s', $clientId, $organizerId, $e->getMessage()));
            return;
        }

        if ($response->getStatusCode() !== 204) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, status code %s', $clientId, $organizerId, $response->getStatusCode()));
            return;
        }

        $this->logger->info(sprintf('Gave %s permission to uitpas organisation %s', $clientId, $organizerId));
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
                    UiTPASPermissions::cases()
                ),
            ],
        ];
    }

    /** @throws GuzzleException */
    private function sendWithBearer(RequestInterface $request, ClientCredentialsContext $credentials): ResponseInterface
    {
        $token = $this->tokenStrategy->fetchToken($credentials);
        $request = $request
            ->withUri(new Uri($this->getEndpoint($credentials) . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $token
            );

        return $this->client->send($request);
    }

    private function getEndpoint(ClientCredentialsContext $keycloakClient): string
    {
        if ($keycloakClient->environment === Environment::Testing) {
            return $this->testApiEndpoint;
        }

        return $this->prodApiEndpoint;
    }

    /** @return string[] */
    public function fetchPermissions(Realm $realm, Client $keycloakClient, string $organizerId): array
    {
        $myRequest = new Request('GET', 'permissions/' . $keycloakClient->clientId);

        $response = $this->sendWithBearer(
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
