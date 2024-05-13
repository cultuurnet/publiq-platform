<?php

declare(strict_types=1);

namespace Tests\Keycloak\Service;

use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Keycloak\Service\IntegrationToKeycloakClientConverter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\IntegrationHelper;

final class IntegrationToKeycloakClientConverterTest extends TestCase
{
    use IntegrationHelper;

    /**
     * @dataProvider integrationDataProvider
     */
    public function test_integration_converted_to_keycloak_format(IntegrationPartnerStatus $partnerStatus, bool $serviceAccountsEnabled, bool $standardFlowEnabled): void
    {
        $id = Uuid::uuid4();

        $integration = $this->givenThereIsAnIntegration($id, ['partnerStatus' => $partnerStatus]);

        $convertedData = IntegrationToKeycloakClientConverter::convert($id, $integration);

        $this->assertIsArray($convertedData);
        $this->assertEquals('openid-connect', $convertedData['protocol']);
        $this->assertEquals($id->toString(), $convertedData['id']);
        $this->assertEquals($integration->id->toString(), $convertedData['clientId']);
        $this->assertEquals($integration->name, $convertedData['name']);
        $this->assertEquals($integration->description, $convertedData['description']);
        $this->assertEquals($serviceAccountsEnabled, $convertedData['serviceAccountsEnabled']);
        $this->assertEquals($standardFlowEnabled, $convertedData['standardFlowEnabled']);
        $this->assertFalse($convertedData['publicClient']);
        $this->assertFalse($convertedData['authorizationServicesEnabled']);
        $this->assertFalse($convertedData['implicitFlowEnabled']);
        $this->assertFalse($convertedData['directAccessGrantsEnabled']);
        $this->assertFalse($convertedData['alwaysDisplayInConsole']);
        $this->assertTrue($convertedData['frontchannelLogout']);
    }

    public static function integrationDataProvider(): array
    {
        return [
            [IntegrationPartnerStatus::THIRD_PARTY, true, false],
            [IntegrationPartnerStatus::FIRST_PARTY, false, true],
        ];
    }
}
