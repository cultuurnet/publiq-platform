<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use Illuminate\Support\Facades\Session;

final class ProjectAanvraagUrl
{
    public static function getForIntegration(Integration $integration): string
    {
        $idToken = Session::get('id_token');

        return self::getBaseUrlForIntegrationStatus($integration->status) . 'project/' . $integration->id . '/widget/?idToken=' . $idToken;
    }

    public static function getBaseUrlForIntegrationStatus(IntegrationStatus $integrationStatus): string
    {
        $stage = $integrationStatus === IntegrationStatus::Active ? 'live' : 'test';

        return config('project_aanvraag.base_uri.' . $stage);
    }
}
