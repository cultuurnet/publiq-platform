<?php

declare(strict_types=1);

namespace App\Keycloak;

final class KeycloakConfig
{
    public const KEYCLOAK_LOGIN_ENABLED = 'keycloak.loginEnabled';
    public const KEYCLOAK_CREATION_ENABLED = 'keycloak.creationEnabled';
    public const KEYCLOAK_DOMAIN = 'keycloak.login.domain';
    public const KEYCLOAK_CLIENT_ID = 'keycloak.login.clientId';
    public const KEYCLOAK_CLIENT_SECRET = 'keycloak.login.clientSecret';
    public const KEYCLOAK_REALM_NAME = 'keycloak.login.realmName';
    public const KEYCLOAK_LOGIN_PARAMETERS = 'keycloak.login.parameters';
}
