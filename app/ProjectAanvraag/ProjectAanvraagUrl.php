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
        $stage = $integration->status === IntegrationStatus::Active ? 'live' : 'test';
        $base = config('project_aanvraag.base_uri.' . $stage);

        return $base . 'project/' . $integration->id . '/widget/?idToken=' . $idToken;
    }
}
