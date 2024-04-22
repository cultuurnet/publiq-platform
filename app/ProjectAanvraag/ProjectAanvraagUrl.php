<?php

declare(strict_types=1);

namespace App\ProjectAanvraag;

use App\Domain\Integrations\Integration;
use Illuminate\Support\Facades\Session;

final class ProjectAanvraagUrl
{
    public static function getForIntegration(Integration $integration): string
    {
        $idToken = Session::get('id_token');

        return self::getBaseUri() . 'project/' . $integration->id . '/widget/?idToken=' . $idToken;
    }

    public static function getBaseUri(): string
    {
        return config('project_aanvraag.base_uri');
    }
}
