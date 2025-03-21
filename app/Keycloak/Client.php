<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Client
{
    public function __construct(
        public UuidInterface $id,
        public UuidInterface $integrationId,
        public string $clientId,
        public string $clientSecret,
        public Environment $environment,
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

        return new self(
            Uuid::fromString($data['id']),
            $integrationId,
            $data['clientId'],
            $data['secret'],
            $realm->environment,
        );
    }
}
