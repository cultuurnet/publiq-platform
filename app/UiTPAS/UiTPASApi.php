<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\TokenStrategy;
use App\Domain\Integrations\Environment;
use App\Domain\UdbUuid;
use App\Json;
use App\UiTPAS\Dto\UiTPASPermission;
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

    public function addPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): bool
    {
        return $this->updatePermissions(
            $context,
            $organizerId,
            $clientId,
            function (array $permissions) use ($organizerId): array {
                $permissions[] = $this->withBody($organizerId);
                return $permissions;
            },
            'Gave %s permission to uitpas organisation %s'
        );
    }

    public function deleteAllPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): bool
    {
        return $this->updatePermissions(
            $context,
            $organizerId,
            $clientId,
            function (array $permissions) use ($organizerId): array {
                return array_values(array_filter($permissions, function ($permission) use ($organizerId) {
                    return $permission['organizer']['id'] !== $organizerId->toString();
                }));
            },
            'Removed organisation %s permissions for organizer %s'
        );
    }

    private function updatePermissions(
        ClientCredentialsContext $context,
        UdbUuid $organizerId,
        string $clientId,
        callable $updateCallback,
        string $successLogMessage
    ): bool {
        try {
            $response = $this->sendWithBearer(
                new Request('GET', 'permissions/' . $clientId),
                $context
            );

            $permissions = Json::decodeAssociatively($response->getBody()->getContents());

            foreach($permissions as $permission) {
                if (isset($permission['organizer']['id']) && $permission['organizer']['id'] === $organizerId->toString()) {
                    // Permission already exists, no need to update
                    return true;
                }
            }

            $updatedPermissions = $updateCallback($permissions);

            $request = new Request('PUT', 'permissions/' . $clientId, [
                'Accept' => 'application/problem+json',
                'Content-Type' => 'application/json',
            ], Json::encode($updatedPermissions));

            $response = $this->sendWithBearer($request, $context);

            if ($response->getStatusCode() !== 204) {
                $this->logger->error(
                    sprintf('Failed to give %s permission to uitpas organisation %s, status code %s', $clientId, $organizerId, $response->getStatusCode())
                );
                return false;
            }

            $this->logger->info(sprintf($successLogMessage, $clientId, $organizerId));
            return true;

        } catch (GuzzleException $e) {
            $this->logger->error(sprintf(
                'Failed to give %s permission to uitpas organisation %s, error %s',
                $clientId,
                $organizerId,
                $e->getMessage()
            ));
            return false;
        }
    }

    private function withBody(UdbUuid $organizerId): array
    {
        return [
            'organizer' => [
                'id' => $organizerId->toString(),
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

    public function fetchPermissions(ClientCredentialsContext $context, UdbUuid $organisationId, string $clientId): ?UiTPASPermission
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
            if ($uiTPASPermission->organizerId->toString() === $organisationId->toString()) {
                return $uiTPASPermission;
            }
        }

        return null;
    }
}
