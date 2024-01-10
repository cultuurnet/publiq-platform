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

        return self::getStatusBaseUri($integration->status) . 'project/' . $integration->id . '/widget/?idToken=' . $idToken;
    }

    public static function getStatusBaseUri(IntegrationStatus $integrationStatus): mixed
    {
        $stage = $integrationStatus === IntegrationStatus::Active ? 'live' : 'test';

        return config('project_aanvraag.base_uri.' . $stage);
    }
}
