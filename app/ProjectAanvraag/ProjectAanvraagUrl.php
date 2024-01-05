<?php

namespace App\ProjectAanvraag;

use App\Domain\Integrations\Integration;
use Illuminate\Support\Facades\Session;

class ProjectAanvraagUrl
{
    public static function getForIntegration(Integration $integration): string
    {
        $idToken = Session::get('id_token');
        $base = config('project_aanvraag.base_uri');

        return $base . 'project/' . $integration->id . '/widget/?idToken=' . $idToken;
    }
}
