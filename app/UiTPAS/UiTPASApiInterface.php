<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\UdbUuid;
use App\UiTPAS\Dto\UiTPASPermissions;

interface UiTPASApiInterface
{
    public function addPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): bool;

    public function fetchPermissions(ClientCredentialsContext $context, UdbUuid $organizerId, string $clientId): UiTPASPermissions;
}
