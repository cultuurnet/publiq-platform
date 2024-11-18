<?php

declare(strict_types=1);

namespace App\Keycloak\Converters;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationUrlType;

/* Converts integration urls in the correct Keycloak API format */

final readonly class IntegrationUrlConverter
{
    public static function buildCallbackUrls(Integration $integration, Environment $environment): array
    {
        if ($integration->partnerStatus !== IntegrationPartnerStatus::FIRST_PARTY) {
            // Only first parties can have redirect uri configured.
            return [];
        }

        $urls = [];

        $callbackUrls = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Callback, $environment);
        foreach ($callbackUrls as $url) {
            $urls[] = $url->url;
        }

        return $urls;
    }

    public static function buildLoginUrl(Integration $integration, Environment $environment): string
    {
        if ($integration->partnerStatus !== IntegrationPartnerStatus::FIRST_PARTY) {
            // Only first parties can have redirect uri configured.
            return '';
        }

        $loginUrl = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Login, $environment);
        if (!isset($loginUrl[0])) {
            return '';
        }

        return $loginUrl[0]->url;
    }

    public static function buildLogoutUrls(Integration $integration, Environment $environment): string
    {
        if ($integration->partnerStatus !== IntegrationPartnerStatus::FIRST_PARTY) {
            // Only first parties can have redirect uri configured.
            return '';
        }

        $logoutUrls = $integration->urlsForTypeAndEnvironment(IntegrationUrlType::Logout, $environment);
        return implode('##', array_map(static fn ($url) => $url->url, $logoutUrls));
    }
}
