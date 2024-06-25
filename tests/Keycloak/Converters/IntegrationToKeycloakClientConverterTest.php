<?php

declare(strict_types=1);

namespace Tests\Keycloak\Converters;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Keycloak\Converters\IntegrationToKeycloakClientConverter;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\TestCase;

final class IntegrationToKeycloakClientConverterTest extends TestCase
{
    use CreatesIntegration;

    /**
     * @dataProvider integrationDataProvider
     */
    public function test_integration_converted_to_keycloak_format(IntegrationPartnerStatus $partnerStatus, bool $serviceAccountsEnabled, bool $standardFlowEnabled): void
    {
        $id = Uuid::uuid4();
        $clientId = Uuid::uuid4()->toString();

        $integration = $this->givenThereIsAnIntegration($id, ['partnerStatus' => $partnerStatus]);

        $convertedData = IntegrationToKeycloakClientConverter::convert($id, $integration, $clientId, Environment::Acceptance);

        $this->assertIsArray($convertedData);
        $this->assertEquals('openid-connect', $convertedData['protocol']);
        $this->assertEquals($id->toString(), $convertedData['id']);
        $this->assertEquals($clientId, $convertedData['clientId']);
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
        $this->assertEquals([
            'origin' => 'publiq-platform',
            'use.refresh.tokens' => true,
            'post.logout.redirect.uris' => '',
        ], $convertedData['attributes']);
        $this->assertEquals('', $convertedData['baseUrl']);
        $this->assertEquals([], $convertedData['redirectUris']);
        $this->assertEquals([
            '+',
            'https://docs.publiq.be',
            'https://publiq.stoplight.io',
        ], $convertedData['webOrigins']);
    }

    public static function integrationDataProvider(): array
    {
        return [
            [IntegrationPartnerStatus::THIRD_PARTY, true, false],
            [IntegrationPartnerStatus::FIRST_PARTY, false, true],
        ];
    }

    public function test_combining_keycloak_convert_with_configured_uris(): void
    {
        $id = Uuid::uuid4();
        $clientId = Uuid::uuid4()->toString();

        $integration = $this->givenThereIsAnIntegration($id, ['partnerStatus' => IntegrationPartnerStatus::FIRST_PARTY]);
        $integration = $integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout2'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback2'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login1'),
        );

        $convertedData = IntegrationToKeycloakClientConverter::convert($id, $integration, $clientId, Environment::Acceptance);
        $this->assertEquals([
            'origin' => 'publiq-platform',
            'use.refresh.tokens' => true,
            'post.logout.redirect.uris' => 'https://example.com/logout1#https://example.com/logout2',
        ], $convertedData['attributes']);
        $this->assertEquals('https://example.com/login1', $convertedData['baseUrl']);
        $this->assertEquals(['https://example.com/callback1', 'https://example.com/callback2'], $convertedData['redirectUris']);
    }
}
