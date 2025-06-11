<?php

declare(strict_types=1);

namespace App\Api;

use App\Domain\Integrations\Environment;

final readonly class ClientCredentialsContext
{
    public function __construct(
        public Environment $environment,
        public string $baseUrl,
        public string $clientId,
        public string $clientSecret,
        public string $realmName
    ) {
    }

    public function getCacheKey(): string
    {
        return $this->environment->value . $this->realmName . $this->clientId;
    }
}
