<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateIntegrationRequest;
use App\Domain\Integrations\Integration;

final class UpdateIntegrationMapper
{
    public static function map(UpdateIntegrationRequest $request, Integration $currentIntegration): Integration
    {
        return new Integration(
            $currentIntegration->id,
            $currentIntegration->type,
            $request->input('integrationName') ?? $currentIntegration->name,
            $request->input('description') ?? $currentIntegration->description,
            $currentIntegration->subscriptionId,
            $currentIntegration->status,
            $currentIntegration->partnerStatus
        );
    }
}
