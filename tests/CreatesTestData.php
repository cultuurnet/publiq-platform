<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Keycloak\Client;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait CreatesTestData
{
    public function givenThereIsAnIntegration(UuidInterface $integrationId, array $options = []): Integration
    {
        return new Integration(
            $integrationId,
            $options['type'] ?? IntegrationType::SearchApi,
            $options['name'] ?? 'Mock Integration',
            $options['description'] ?? 'Mock description',
            Uuid::uuid4(),
            $options['status'] ?? IntegrationStatus::Draft,
            $options['partnerStatus'] ?? IntegrationPartnerStatus::THIRD_PARTY,
        );
    }

    private function givenThereIsAKeycloakClient(Integration $integration, Environment $environment = Environment::Production): Client
    {
        return new Client(
            Uuid::uuid4(),
            $integration->id,
            'test-client-' . $environment->value,
            'test-secret-' . $environment->value,
            $environment
        );
    }
}
