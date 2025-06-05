<?php

declare(strict_types=1);

namespace App\Uitpas;

use App\Domain\Integrations\Environment;
use App\Json;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\Realm;
use App\Keycloak\TokenStrategy\TokenStrategy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final readonly class UitpasApi implements UitpasApiInterface
{
    public function __construct(
        private KeycloakGuzzleClient $keycloakHttpClient,
        private ClientInterface $client,
        private TokenStrategy $tokenStrategy,
        private LoggerInterface $logger,
        private string $testApiEndpoint,
        private string $prodApiEndpoint,
    ) {
    }

    public function addPermissions(Realm $realm, string $organizerId, string $clientId): void
    {
        $request = new Request('PUT', 'permissions/' . $clientId, [
            'Accept' => 'application/problem+json',
            'Content-Type' => 'application/json',
        ], Json::encode($this->withBody($organizerId)));

        try {
            $response = $this->sendWithBearer(
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
                    UitpasPermissions::cases()
                ),
            ],
        ];
    }

    /** @throws GuzzleException */
    private function sendWithBearer(RequestInterface $request, Realm $realm): ResponseInterface
    {
        $token = $this->tokenStrategy->fetchToken($this->keycloakHttpClient, $realm);
        $request = $request
            ->withUri(new Uri($this->getEndpoint($realm) . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $token
            );

        return $this->client->send($request);
    }

    private function getEndpoint(Realm $keycloakClient): string
    {
        if ($keycloakClient->environment === Environment::Testing) {
            return $this->testApiEndpoint;
        }

        return $this->prodApiEndpoint;
    }
}
