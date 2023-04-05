<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

/**
 * More information about the different URLs can be found inside the Auth0 docs
 * @see https://auth0.com/docs/get-started/applications/application-settings#application-uris
 */
enum IntegrationUrlType: string
{
    case Login = 'login';
    case Callback = 'callback';
    case Logout = 'logout';
}
