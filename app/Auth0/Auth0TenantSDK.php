<?php

declare(strict_types=1);

namespace App\Auth0;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Json;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\API\ManagementInterface;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

final class Auth0TenantSDK
{
    private const GRANTS = [ // Determines in what ways the client can request access tokens
        'authorization_code', // Enables the user login flow (but `callbacks` still required to make it work - see below)
        'refresh_token', // Makes it possible to request and use refresh tokens when using the authorization_code grant type
        'client_credentials', // Enables the client credentials flow (m2m tokens)
    ];

    private ManagementInterface $management;

    public function __construct(
        public readonly Auth0Tenant $auth0Tenant,
        private readonly SdkConfiguration $sdkConfiguration
    ) {
        $this->init($sdkConfiguration);
    }

    public function createClientForIntegration(Integration $integration): Auth0Client
    {
        try {
            return $this->createClientForIntegrationGuarded($integration);
        } catch (Auth0Unauthorized) {
            $this->initToken($this->sdkConfiguration);
            return $this->createClientForIntegrationGuarded($integration);
        }
    }

    private function createClientForIntegrationGuarded(Integration $integration): Auth0Client
    {
        $apis = match ($integration->type) {
            IntegrationType::SearchApi, IntegrationType::Widgets => 'sapi',
            IntegrationType::EntryApi => 'entry sapi',
            IntegrationType::UiTPAS => 'ups entry sapi',
        };

        $clientResponse = $this->management->clients()->create(
            $this->clientName($integration),
            [
                'app_type' => 'regular_web', // The app type has no real meaning/implications, but regular_web is the most logical for generic clients for integrators
                'client_metadata' => [
                    'publiq-apis' => $apis,
                    'partner-status' => $integration->partnerStatus->value,
                ],
                'token_endpoint_auth_method' => 'client_secret_post', // To require the client to authenticate with their client secret in a JSON body in "POST /oauth/token" (instead of HTTP basic auth or no secret)
                'cross_origin_auth' => true, // Required to make it possible for SPAs to get tokens using the PKCE flow
                'is_token_endpoint_ip_header_trusted' => false, // Should be false to avoid security issues with X-Forwarded-For headers
                'is_first_party' => true, // We cannot customize the consent step that would be shown to users when logging in if this was set to false
                'oidc_conformant' => true, // Needed to enable refresh token rotation (see below)
                'grant_types' => self::GRANTS,
                'callbacks' => $this->getCallbackUrls($integration),
                'allowed_logout_urls' => $this->getLogoutUrls($integration),
                'initiate_login_uri' => $this->getLoginUrl($integration),
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
        $this->guardResponseStatus(201, $clientResponse);

        $data = Json::decodeAssociatively($clientResponse->getBody()->getContents());
        $clientId = $data['client_id'];
        $clientSecret = $data['client_secret'];

        $grantResponse = $this->management->clientGrants()->create($clientId, 'https://api.publiq.be');
        $this->guardResponseStatus(201, $grantResponse);

        return new Auth0Client(Uuid::uuid4(), $integration->id, $clientId, $clientSecret, $this->auth0Tenant);
    }

    public function updateClient(Integration $integration, Auth0Client $auth0Client): void
    {
        $body = [
            'name' => $this->clientName($integration),
            'callbacks' => $this->getCallbackUrls($integration),
            'allowed_logout_urls' => $this->getLogoutUrls($integration),
            'client_metadata' => [
                'partner-status' => $integration->partnerStatus->value,
            ],
            'initiate_login_uri' => $this->getLoginUrl($integration),
        ];

        $this->callApiWithTokenRefresh(
            fn () => $this->management->clients()->update(
                $auth0Client->clientId,
                $body
            )
        );
    }

    public function blockClient(Auth0Client $auth0Client): void
    {
        $this->callApiWithTokenRefresh(
            fn () => $this->management->clients()->update(
                $auth0Client->clientId,
                ['grant_types' => []]
            )
        );
    }

    public function activateClient(Auth0Client $auth0Client): void
    {
        $this->callApiWithTokenRefresh(
            fn () => $this->management->clients()->update(
                $auth0Client->clientId,
                ['grant_types' => self::GRANTS]
            )
        );
    }

    public function findGrantsOnClient(Auth0Client $auth0Client): array
    {
        $response = $this->management->clients()->get($auth0Client->clientId);

        $json = json_decode($response->getBody()->getContents());

        if(! is_object($json) || ! property_exists($json, 'grant_types')) {
            return [];
        }

        Log::info('Grants for client ' . $auth0Client->clientId . ': ' . implode(', ', $json->grant_types));

        return $json->grant_types;
    }

    private function callApiWithTokenRefresh(callable $callApi): void
    {
        try {
            $this->guardResponseStatus(200, $callApi());
        } catch (Auth0Unauthorized) {
            $this->initToken($this->sdkConfiguration);
            $callApi();
        }
    }

    private function guardResponseStatus(int $expectedStatusCode, ResponseInterface $response): void
    {
        if ($response->getStatusCode() === 401) {
            throw new Auth0Unauthorized();
        }

        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw Auth0SDKException::forResponse($response);
        }
    }

    private function init(SdkConfiguration $sdkConfiguration): void
    {
        $auth0 = new Auth0($sdkConfiguration);

        if (!$auth0->configuration()->hasManagementToken()) {
            $this->initToken($sdkConfiguration);
        }

        $this->management = $auth0->management();
    }

    private function initToken(SdkConfiguration $sdkConfiguration): void
    {
        $auth0 = new Auth0($sdkConfiguration);

        $response = $auth0->authentication()->clientCredentials($sdkConfiguration->getAudience());
        $data = Json::decodeAssociatively((string) $response->getBody());
        $auth0->configuration()->setManagementToken($data['access_token']);
    }

    private function clientName(Integration $integration): string
    {
        return $integration->name . ' (via publiq platform)';
    }

    private function getLoginUrl(Integration $integration): ?string
    {
        $loginUrls = $integration->urlsForTypeAndEnvironment(
            IntegrationUrlType::Login,
            Environment::from($this->auth0Tenant->value)
        );

        if (count($loginUrls) === 0) {
            return null;
        }

        return reset($loginUrls)->url;
    }

    private function getCallbackUrls(Integration $integration): array
    {
        $urls = $this->getUrls(IntegrationUrlType::Callback, $integration);
        $urls[] = 'https://oauth.pstmn.io/v1/callback'; // Allow logins via Postman for easy debugging/testing/experimentation
        return $urls;
    }

    private function getLogoutUrls(Integration $integration): array
    {
        return $this->getUrls(IntegrationUrlType::Logout, $integration);
    }

    private function getUrls(IntegrationUrlType $integrationUrlType, Integration $integration): array
    {
        $integrationUrls = $integration->urlsForTypeAndEnvironment(
            $integrationUrlType,
            Environment::from($this->auth0Tenant->value)
        );

        if (count($integrationUrls) === 0) {
            return [];
        }

        return array_values(
            array_map(
                fn (IntegrationUrl $integrationUrl) => $integrationUrl->url,
                $integrationUrls
            )
        );
    }
}
