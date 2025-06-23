<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Udb3Uuid;
use App\UiTPAS\Dto\UiTPASPermissions;

interface UiTPASApiInterface
{
    public function addPermissions(ClientCredentialsContext $context, Udb3Uuid $organizerId, string $clientId): bool;

    public function fetchPermissions(ClientCredentialsContext $context, Udb3Uuid $organizerId, string $clientId): UiTPASPermissions;
}
