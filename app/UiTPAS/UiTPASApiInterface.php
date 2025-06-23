<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\UiTPAS\Dto\UiTPASPermission;

interface UiTPASApiInterface
{
    public function addPermissions(ClientCredentialsContext $context, string $organizerId, string $clientId): bool;

    public function fetchPermissions(ClientCredentialsContext $context, string $organisationId, string $clientId): ?UiTPASPermission;
}
