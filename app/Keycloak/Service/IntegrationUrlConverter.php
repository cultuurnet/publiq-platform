<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Keycloak\Client;

/* Converts integration urls in the correct Keycloak API format */
final readonly class IntegrationUrlConverter
{
    public static function convert(Integration $integration, Client $client): array
    {
        // Empty start construct to make all urls empty at the beginning of each update
        $urls = [
            'baseUrl' => '',
            'redirectUris' => [],
            'attributes' => ['post.logout.redirect.uris' => ''],
            'webOrigins' => [
                '+', // This permits all origins of Valid Redirect URIs for CORS checks
            ],
        ];

        if ($integration->partnerStatus !== IntegrationPartnerStatus::FIRST_PARTY) {
            // Only first parties can have redirect uri configured.
            return $urls;
        }

        $urls = self::buildCallbackUrl($integration, $client, $urls);
        $urls = self::buildLoginUrls($integration, $client, $urls);
        return self::buildLogoutUrls($integration, $client, $urls);
    }

    private static function buildCallbackUrl(Integration $integration, Client $client, array $urls): array
    {
        $callbackUrl = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Callback, $client->realm->environment);
        if (isset($callbackUrl[0]) && $callbackUrl[0] instanceof IntegrationUrl) {
            $urls['baseUrl'] = $callbackUrl[0]->url;
        }

        return $urls;
    }

    private static function buildLoginUrls(Integration $integration, Client $client, array $urls): array
    {
        $loginUrls = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Login, $client->realm->environment);
        foreach ($loginUrls as $loginUrl) {
            $urls['redirectUris'][] = $loginUrl->url;
        }

        return $urls;
    }

    private static function buildLogoutUrls(Integration $integration, Client $client, array $urls): array
    {
        $logoutUrls = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Logout, $client->realm->environment);
        $urls['attributes']['post.logout.redirect.uris'] = implode('#', array_map(static fn ($url) => $url->url, $logoutUrls));

        return $urls;
    }
}
