<?php

declare(strict_types=1);

namespace App\Keycloak\Service;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use Ramsey\Uuid\UuidInterface;

final class IntegrationToKeycloakClientConverter
{
    public static function convert(UuidInterface $id, Integration $integration): array
    {
        return [
            /** @todo Ask Erwin how to set Metadata to mark this as created by Publiq Platform */
            'protocol' => 'openid-connect',
            'id' => $id->toString(),
            'clientId' => $integration->id->toString(),
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
