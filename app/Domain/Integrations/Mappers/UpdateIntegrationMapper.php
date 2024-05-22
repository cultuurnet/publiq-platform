<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateIntegrationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Website;

final class UpdateIntegrationMapper
{
    public static function map(UpdateIntegrationRequest $request, Integration $currentIntegration): Integration
    {
        $integration = new Integration(
            $currentIntegration->id,
            $currentIntegration->type,
            $request->input('integrationName') ?? $currentIntegration->name,
            $request->input('description') ?? $currentIntegration->description,
            $currentIntegration->subscriptionId,
            $currentIntegration->status,
            $currentIntegration->partnerStatus
        );

        if (!is_null($request->input('website'))) {
            $integration = $integration->withWebsite(new Website($request->input('website')));
        }

        return $integration;
    }
}
