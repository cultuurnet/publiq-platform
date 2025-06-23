<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\TokenStrategy;
use App\Domain\Integrations\Environment;
use App\Json;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\Dto\UiTPASPermissions;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Log;
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

    public function addPermissions(ClientCredentialsContext $context, string $organizerId, string $clientId): bool
    {
        $response = $this->sendWithBearer(
            new Request('GET', 'permissions/' . $clientId),
            $context
        );
        $currentPermissions = Json::decodeAssociatively($response->getBody()->getContents());
        $currentPermissions[] = $this->withBody($organizerId);

        $request = new Request('PUT', 'permissions/' . $clientId, [
            'Accept' => 'application/problem+json',
            'Content-Type' => 'application/json',
        ], Json::encode($currentPermissions));

        try {
            $response = $this->sendWithBearer(
                $request,
                $context
            );
        } catch (GuzzleException $e) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, error %s', $clientId, $organizerId, $e->getMessage()));
            return false;
        }

        if ($response->getStatusCode() !== 204) {
            $this->logger->error(sprintf('Failed to give %s permission to uitpas organisation %s, status code %s', $clientId, $organizerId, $response->getStatusCode()));
            return false;
        }

        $this->logger->info(sprintf('Gave %s permission to uitpas organisation %s', $clientId, $organizerId));

        return true;
    }

    private function withBody(string $organizerId): array
    {
        return [
            'organizer' => [
                'id' => $organizerId,
            ],
            'permissionDetails' => array_map(
                static fn ($id) => ['id' => $id],
                [
                    'CHECKINS_WRITE',
                    'EVENTS_READ',
                    'EVENTS_UPDATE',
                    'TICKETSALES_REGISTER',
                    'TICKETSALES_SEARCH',
                    'ORGANIZERS_SEARCH',
                    'TARIFFS_READ',
                    'MEMBERSHIP_PRICES_READ',
                    'PASSES_READ',
                    'PASSES_INSZNUMBERS_READ',
                    'PASSES_CHIPNUMBERS_READ',
                    'REWARDS_READ',
                    'REWARDS_WRITE',
                    'REWARDS_REDEEM',
                    'REWARDS_PASSHOLDERS_READ',
                ]
            ),
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

    public function fetchPermissions(ClientCredentialsContext $context, string $organisationId, string $clientId): ?UiTPASPermission
    {
        $response = $this->sendWithBearer(
            new Request('GET', 'permissions/' . $clientId),
            $context
        );

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(sprintf('Failed to fetch permissions: %s', $response->getBody()));
            return null;
        }

        $uiTPASPermissions = UiTPASPermissions::loadFromJson($response->getBody()->getContents());

        foreach ($uiTPASPermissions as $uiTPASPermission) {
            Log::error('permissions ' . $uiTPASPermission->organizerId . ' === ' . $organisationId);
            if ($uiTPASPermission->organizerId === $organisationId) {
                return $uiTPASPermission;
            }
        }

        return null;
    }
}
