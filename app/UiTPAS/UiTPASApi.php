<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\TokenStrategy;
use App\Domain\Integrations\Environment;
use App\Json;
use App\UiTPAS\Dto\UiTPASPermissions;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
                    UiTPASPermissionsDefaultValues::cases()
                ),
            ],
        ];
    }

    /** @throws GuzzleException */
    private function sendWithBearer(RequestInterface $request, ClientCredentialsContext $context): ResponseInterface
    {
        $token = $this->tokenStrategy->fetchToken($context);
        $request = $request
            ->withUri(new Uri($this->getEndpoint($context) . $request->getUri()))
            ->withAddedHeader(
                'Authorization',
                'Bearer ' . $token
            );

        return $this->client->send($request);
    }

    private function getEndpoint(ClientCredentialsContext $context): string
    {
        if ($context->environment === Environment::Testing) {
            return $this->testApiEndpoint;
        }

        return $this->prodApiEndpoint;
    }

    public function fetchPermissions(ClientCredentialsContext $context, string $organisationId, string $clientId): UiTPASPermissions
    {
        $response = $this->sendWithBearer(
            new Request('GET', 'permissions/' . $clientId),
            $context
        );

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(sprintf('Failed to fetch permissions: %s', $response->getBody()));
            return new UiTPASPermissions();
        }

        return UiTPASPermissions::loadFromJson($response->getBody()->getContents());
    }
}
