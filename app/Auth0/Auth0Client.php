<?php

declare(strict_types=1);

namespace App\Auth0;

use Ramsey\Uuid\UuidInterface;

final class Auth0Client
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly Auth0Tenant $tenant
    ) {
    }
}
