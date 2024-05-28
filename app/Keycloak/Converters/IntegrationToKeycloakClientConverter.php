<?php

declare(strict_types=1);

namespace App\Keycloak\Converters;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use Ramsey\Uuid\UuidInterface;

final class IntegrationToKeycloakClientConverter
{
    public static function convert(UuidInterface $id, Integration $integration, UuidInterface $clientId): array
    {
        return [
            'protocol' => 'openid-connect',
            'id' => $id->toString(),
            'clientId' => $clientId->toString(),
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
        ];
    }
}
