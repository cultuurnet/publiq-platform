<?php

declare(strict_types=1);

namespace App\Keycloak;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Client
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public UuidInterface $clientId,
        public string $clientSecret,
        public Realm $realm
    ) {
    }

    public static function createFromJson(Realm $realm, UuidInterface $integrationId, array $data): self
    {
        if (empty($data['secret'])) {
            throw new InvalidArgumentException('Missing secret');
        }

        //@todo Currently IntegrationId is always equal to clientId. is that something we want?
        return new self(
            Uuid::fromString($data['id']),
            $integrationId,
            Uuid::fromString($data['clientId']),
            $data['secret'],
            $realm,
        );
    }

    public function getKeycloakUrl(string $baseUrl): string
    {
        return sprintf('%s/admin/master/console/#/%s/clients/%s/settings', $baseUrl, $this->realm->internalName, $this->clientId->toString());
    }
}
