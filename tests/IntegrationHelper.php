<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait IntegrationHelper
{
    public function createIntegration(UuidInterface $integrationId, array $options = []): Integration
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
}
