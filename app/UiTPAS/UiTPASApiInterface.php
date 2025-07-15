<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\UiTPAS\Dto\UiTPASPermission;
use App\Domain\UdbUuid;

interface UiTPASApiInterface
{
    public function addPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): bool;

    public function fetchPermissions(ClientCredentialsContext $context, UdbUuid $organisationId, string $clientId): ?UiTPASPermission;

    public function deleteAllPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): bool;
}
