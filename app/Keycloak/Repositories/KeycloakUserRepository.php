<?php

declare(strict_types=1);

namespace App\Keycloak\Repositories;

use App\Domain\Auth\Repositories\UserRepository;
use App\Json;
use App\Keycloak\Client\KeycloakGuzzleClient;
use App\Keycloak\Realm;
use GuzzleHttp\Psr7\Request;

final class KeycloakUserRepository implements UserRepository
{
    private KeycloakGuzzleClient $client;
    private Realm $realm;

    public function __construct(
        KeycloakGuzzleClient $client,
        Realm $realm
    ) {
        $this->client = $client;
        $this->realm = $realm;
    }

    public function findUserIdByEmail(string $email): ?string
    {
        $emailQuery = http_build_query([
            'email' => $email,
            'exact' => 'true',
        ]);

        $response = $this->client->sendWithBearer(
            new Request(
                'GET',
                sprintf('admin/realms/%s/users?%s', $this->realm->internalName, $emailQuery),
            ),
            $this->realm
        );

        $users = Json::decodeAssociatively($response->getBody()->getContents());

        if (empty($users)) {
            return null;
        }

        return $users[0]['id'] ?? $users[0]['attributes']['uitidv1id'][0];
    }
}
