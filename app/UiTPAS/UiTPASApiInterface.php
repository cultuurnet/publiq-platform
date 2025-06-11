<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;

interface UiTPASApiInterface
{
    public function addPermissions(ClientCredentialsContext $context, string $organizerId, string $clientId): void;

    /** @return string[] */
    public function fetchPermissions(ClientCredentialsContext $context, string $organizerId): array;
}
