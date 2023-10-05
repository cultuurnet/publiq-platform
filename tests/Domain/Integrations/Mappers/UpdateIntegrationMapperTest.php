<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateIntegrationRequest;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Mappers\UpdateIntegrationMapper;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UpdateIntegrationMapperTest extends TestCase
{
    public function test_it_creates_an_updated_integration_from_request(): void
    {
        $integrationId = '0b7289b5-717c-44c0-8c05-b3d2bbb190ab';

        $inputs = [
            'integrationName' => 'new integration name',
            'description' => 'new integration description',
        ];

        $request = new UpdateIntegrationRequest();
        $request->merge($inputs);

        $currentIntegration = new Integration(
            Uuid::fromString($integrationId),
            IntegrationType::SearchApi,
            'old integration name',
            'old integration description',
            Uuid::fromString('14671dcc-5985-4ed6-beda-6d28cf7a0d96'),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY
        );

        $actual = UpdateIntegrationMapper::map($request, $currentIntegration);

        $expected = new Integration(
            Uuid::fromString($integrationId),
            IntegrationType::SearchApi,
            $inputs['integrationName'],
            $inputs['description'],
            Uuid::fromString('14671dcc-5985-4ed6-beda-6d28cf7a0d96'),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY
        );

        $this->assertEquals($expected, $actual);
    }
}
