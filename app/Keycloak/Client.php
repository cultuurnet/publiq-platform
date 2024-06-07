<?php

declare(strict_types=1);

namespace App\Keycloak;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Facades\App;
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
            Uuid::fromString($data['clientId']),
            $data['secret'],
            $realm->environment,
        );
    }

    public function getKeycloakUrl(): string
    {
        $baseUrl = $this->getRealm()->baseUrl;

        return $baseUrl . 'admin/master/console/#/' . $this->getRealm()->internalName . '/clients/' . $this->id->toString() . '/settings';
    }

    public function getRealm(): Realm
    {
        /** @var Realms $realmCollection */
        $realmCollection = App::get(Realms::class);

        foreach ($realmCollection as $realm) {
            if ($realm->environment === $this->environment) {
                return $realm;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Could not convert environment %s to realm:', $this->environment->value)
        );
    }
}
