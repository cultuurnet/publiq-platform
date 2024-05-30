<?php

declare(strict_types=1);

namespace App\Keycloak;

use Illuminate\Support\Facades\Log;
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
        public Realm $realm,
    ) {
    }

    public static function createFromJson(
        Realm $realm,
        UuidInterface $integrationId,
        array $data
    ): self {
        if (empty($data['secret'])) {
            throw new InvalidArgumentException('Missing secret');
        }

        Log::debug(json_encode($data), $data);

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
        return $baseUrl . 'admin/master/console/#/' . $this->realm->internalName . '/clients/' . $this->id->toString() . '/settings';
    }
}
