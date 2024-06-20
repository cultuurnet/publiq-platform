<?php

declare(strict_types=1);

namespace App\Keycloak;

final class KeycloakConfig
{
    public const IS_ENABLED = 'keycloak.enabled';
    public const LOGIN_REALM_NAME = 'keycloak.login.internalName';
    public const LOGIN_BASE_URL = 'keycloak.login.base_url';
    public const LOGIN_CLIENT_ID = 'keycloak.login.client_id';
    public const LOGIN_CLIENT_SECRET = 'keycloak.login.client_secret';
    public const REDIRECT_URI = 'keycloak.login.redirect_uri';
    public const CERTIFICATE = 'keycloak.certificate';
}
