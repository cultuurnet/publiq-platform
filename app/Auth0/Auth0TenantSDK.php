<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Json;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\API\ManagementInterface;
use RuntimeException;

final class Auth0TenantSDK
{
    private ManagementInterface $management;

    public function __construct(public readonly Auth0Tenant $auth0Tenant, SdkConfiguration $sdkConfiguration)
    {
        $auth0 = new Auth0($sdkConfiguration);

        if (!$auth0->configuration()->hasManagementToken()) {
            $response = $auth0->authentication()->clientCredentials($sdkConfiguration->getAudience());
            $response = Json::decodeAssociatively((string) $response->getBody());
            $auth0->configuration()->setManagementToken($response['access_token']);
        }

        $this->management = $auth0->management();
    }

    public function createClientForIntegration(Integration $integration): Auth0Client
    {
        $name = sprintf('%s (id: %s)', $integration->name, $integration->id->toString());

        $apis = match ($integration->type) {
            IntegrationType::SearchApi, IntegrationType::Widgets => 'sapi',
            IntegrationType::EntryApi => 'entry sapi',
        };

        $response = $this->management->clients()->create(
            $name,
            [
                'app_type' => 'regular_web', // The app type has no real meaning/implications, but regular_web is the most logical for generic clients for integrators
                'client_metadata' => [
                    'publiq-apis' => $apis,
                ],
                'token_endpoint_auth_method' => 'client_secret_post', // To require the client to authenticate with their client secret in a JSON body in "POST /oauth/token" (instead of HTTP basic auth or no secret)
                'cross_origin_auth' => true, // Required to make it possible for SPAs to get tokens using the PKCE flow
                'is_token_endpoint_ip_header_trusted' => false, // Should be false to avoid security issues with X-Forwarded-For headers
                'is_first_party' => true, // We cannot customize the consent step that would be shown to users when logging in if this was set to false
                'oidc_conformant' => true, // Needed to enable refresh token rotation (see below)
                'grant_types' => [ // Determines in what ways the client can request access tokens
                    'authorization_code', // Enables the user login flow (but `callbacks` still required to make it work - see below)
                    'refresh_token', // Makes it possible to request and use refresh tokens when using the authorization_code grant type
                    'client_credentials', // Enables the client credentials flow (m2m tokens)
                ],
                'callbacks' => [
                    'https://oauth.pstmn.io/v1/callback', // Allow logins via Postman for easy debugging/testing/experimentation
                ],
                'allowed_logout_urls' => [],
                'initiate_login_uri' => '',
                'web_origins' => [
                    'https://docs.publiq.be', // Always add this origin to enable CORS requests from the "Try it out!" functionality in Stoplight
                    'https://publiq.stoplight.io', // Always add this origin to enable CORS requests from the "Try it out!" functionality in Stoplight
                ],
                'refresh_token' => [
                    'rotation_type' => 'rotating', // When exchanging a refresh token for a new access token, a new refresh token will also be given
                    'expiration_type' => 'expiring', // Refresh tokens expire
                    'infinite_token_lifetime' => false, // Refresh tokens do not have an infinite lifetime
                    'token_lifetime' => 1209600, // Lifetime of two weeks
                    'infinite_idle_token_lifetime' => true, // Refresh tokens do not expire based on (no) activity
                    'leeway' => 30, // Refresh tokens are only invalidated after they have been rotated for 30s, to avoid race conditions
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        if ($statusCode !== 201) {
            throw new RuntimeException(
                'Auth0 responded with status code ' . $statusCode . ' instead of 201. Response body: ' . $body
            );
        }

        $data = Json::decodeAssociatively($body);
        $clientId = $data['client_id'];
        $clientSecret = $data['client_secret'];

        return new Auth0Client($integration->id, $clientId, $clientSecret, $this->auth0Tenant);
    }
}
