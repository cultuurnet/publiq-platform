<?php

declare(strict_types=1);

/**
 * Please review available configuration options here:
 * https://github.com/auth0/auth0-PHP#configuration-options.
 */
return [
    'enabled' => env('AUTH0_CLIENT_ENABLED', true),

    // Should be assigned either 'api', 'management', or 'webapp' to indicate your application's use case for the SDK.
    // Determines what configuration options will be required.
    'strategy' => env('AUTH0_LOGIN_STRATEGY', \Auth0\SDK\Configuration\SdkConfiguration::STRATEGY_REGULAR),

    // Auth0 domain for your tenant, found in your Auth0 Application settings.
    'domain' => env('AUTH0_LOGIN_DOMAIN'),

    // Auth0 Management API domain for your tenant, found in your Auth0 Application settings.
    'managementDomain' => env('AUTH0_LOGIN_MANAGEMENT_DOMAIN'),

    // If you have configured Auth0 to use a custom domain, configure it here.
    'customDomain' => env('AUTH0_LOGIN_CUSTOM_DOMAIN'),

    // Client ID, found in the Auth0 Application settings.
    'clientId' => env('AUTH0_LOGIN_CLIENT_ID'),

    // Authentication callback URI, as defined in your Auth0 Application settings.
    'redirectUri' => env('AUTH0_LOGIN_REDIRECT_URI', env('APP_URL') . '/callback'),

    // Client Secret, found in the Auth0 Application settings.
    'clientSecret' => env('AUTH0_LOGIN_CLIENT_SECRET'),

    // One or more API identifiers, found in your Auth0 API settings. The SDK uses the first value for building links. If provided, at least one of these values must match the 'aud' claim to validate an ID Token successfully.
    'audience' => env('AUTH0_LOGIN_AUDIENCE'),

    // One or more scopes to request for Tokens. See https://auth0.com/docs/scopes
    'scope' => env('AUTH0_LOGIN_SCOPE'),

    // One or more Organization IDs, found in your Auth0 Organization settings. The SDK uses the first value for building links. If provided, at least one of these values must match the 'org_id' claim to validate an ID Token successfully.
    'organization' => env('AUTH0_LOGIN_ORGANIZATION'),

    // The secret used to derive an encryption key for the user identity in a session cookie and to sign the transient cookies used by the login callback.
    'cookieSecret' => env('AUTH0_LOGIN_COOKIE_SECRET', env('APP_KEY')),

    // How long, in seconds, before cookies expire. If set to 0 the cookie will expire at the end of the session (when the browser closes).
    'cookieExpires' => env('COOKIE_EXPIRES', 0),

    // Cookie domain, for example 'www.example.com', for use with PHP sessions and SDK cookies. Defaults to value of HTTP_HOST server environment information.
    // Note: To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.example.com'.
    'cookieDomain' => env('AUTH0_LOGIN_COOKIE_DOMAIN'),

    // Specifies path on the domain where the cookies will work. Defaults to '/'. Use a single slash ('/') for all paths on the domain.
    'cookiePath' => env('AUTH0_LOGIN_COOKIE_PATH', '/'),

    // Defaults to false. Specifies whether cookies should ONLY be sent over secure connections.
    'cookieSecure' => env('AUTH0_LOGIN_COOKIE_SECURE', false),

    // Named routes within your Laravel application that the SDK may call during stateful requests for redirections.
    'routes' => [
        'home'  => env('AUTH0_LOGIN_ROUTE_HOME', '/'),
        'login' => env('AUTH0_LOGIN_ROUTE_LOGIN', 'login'),
    ],

    // URL parameters to include in the /authorize redirect
    'login_parameters' => env('AUTH0_LOGIN_PARAMETERS', ''),

    // Tenant configuration, used to store/update clients in Auth0.
    // Note that local/staging/acceptance/testing environments of publiq platform should actually use the DEV tenant as
    // replacements for the acc/test/prod tenants. Otherwise, they will create real clients on the acc/test/prod tenants
    // which we do not want.
    'tenants' => [
        'acc' => [
            'domain' => env('AUTH0_ACC_TENANT_DOMAIN'),
            'clientId' => env('AUTH0_ACC_TENANT_CLIENT_ID'),
            'clientSecret' => env('AUTH0_ACC_TENANT_CLIENT_SECRET'),
            'audience' => 'https://' . env('AUTH0_ACC_TENANT_DOMAIN') . '/api/v2/',
            'clientDetailUrlTemplate' => env('AUTH0_ACC_TENANT_CLIENT_DETAILS_URL_TEMPLATE'),
        ],
        'test' => [
            'domain' => env('AUTH0_TEST_TENANT_DOMAIN'),
            'clientId' => env('AUTH0_TEST_TENANT_CLIENT_ID'),
            'clientSecret' => env('AUTH0_TEST_TENANT_CLIENT_SECRET'),
            'audience' => 'https://' . env('AUTH0_TEST_TENANT_DOMAIN') . '/api/v2/',
            'clientDetailUrlTemplate' => env('AUTH0_TEST_TENANT_CLIENT_DETAILS_URL_TEMPLATE'),
        ],
        'prod' => [
            'domain' => env('AUTH0_PROD_TENANT_DOMAIN'),
            'clientId' => env('AUTH0_PROD_TENANT_CLIENT_ID'),
            'clientSecret' => env('AUTH0_PROD_TENANT_CLIENT_SECRET'),
            'audience' => 'https://' . env('AUTH0_PROD_TENANT_DOMAIN') . '/api/v2/',
            'clientDetailUrlTemplate' => env('AUTH0_PROD_TENANT_CLIENT_DETAILS_URL_TEMPLATE'),
        ],
    ],
];
