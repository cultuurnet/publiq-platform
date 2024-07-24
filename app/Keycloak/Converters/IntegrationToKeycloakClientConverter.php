<?php

declare(strict_types=1);

namespace App\Keycloak\Converters;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use Ramsey\Uuid\UuidInterface;

final class IntegrationToKeycloakClientConverter
{
    public static function convert(UuidInterface $id, Integration $integration, string $clientId, Environment $environment): array
    {
        return [
            'protocol' => 'openid-connect',
            'id' => $id->toString(),
            'clientId' => $clientId,
            'name' => $integration->name,
            'description' => $integration->description,
            'publicClient' => false,
            'authorizationServicesEnabled' => false,
            'serviceAccountsEnabled' => $integration->partnerStatus === IntegrationPartnerStatus::THIRD_PARTY,
            'implicitFlowEnabled' => false,
            'directAccessGrantsEnabled' => false,
            'standardFlowEnabled' => $integration->partnerStatus === IntegrationPartnerStatus::FIRST_PARTY,
            'frontchannelLogout' => true,
            'alwaysDisplayInConsole' => false,
            'attributes' => [
                'origin' => 'publiq-platform',
                'use.refresh.tokens' => true,
                'post.logout.redirect.uris' => IntegrationUrlConverter::buildLogoutUrls($integration, $environment),
            ],
            'baseUrl' => IntegrationUrlConverter::buildLoginUrl($integration, $environment),
            'redirectUris' => IntegrationUrlConverter::buildCallbackUrls($integration, $environment),
            'webOrigins' => [
                '+', // This permits all origins of Valid Redirect URIs for CORS checks
                'https://docs.publiq.be', // Always add this origin to enable CORS requests from the "Try it out!" functionality in Stoplight
                'https://publiq.stoplight.io', // Always add this origin to enable CORS requests from the "Try it out!" functionality in Stoplight
            ],
        ];
    }
}
