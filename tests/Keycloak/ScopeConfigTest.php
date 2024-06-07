<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\IntegrationType;
use App\Keycloak\ScopeConfig;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;

final class ScopeConfigTest extends TestCase
{
    use CreatesIntegration;

    private const SEARCH_API_ID = '41255857-b8ad-44ce-9a17-db72540461b7';
    private const ENTRY_API_ID = '824c09c0-2f3a-4fa0-bde2-8bf25c9a5b74';
    private const WIDGETS_ID = 'deb654ab-1324-48ca-9931-1485a456c916';
    private const UITPAS_ID = '0743b1c7-0ea2-46af-906e-fbb6c0317514';

    private ScopeConfig $scopeConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfig = new ScopeConfig(
            Uuid::fromString(self::SEARCH_API_ID),
            Uuid::fromString(self::ENTRY_API_ID),
            Uuid::fromString(self::WIDGETS_ID),
            Uuid::fromString(self::UITPAS_ID)
        );
    }

    /**
     * @dataProvider integrationDataProvider
     */
    public function test_get_scope_id_from_integration(IntegrationType $type, string $expectedScopeId): void
    {
        $actualScopeId = $this->scopeConfig->getScopeIdFromIntegrationType(
            $this->givenThereIsAnIntegration(Uuid::uuid4(), ['type' => $type])
        );

        $this->assertEquals($expectedScopeId, $actualScopeId->toString());
    }

    public static function integrationDataProvider(): array
    {
        return [
            [IntegrationType::SearchApi, self::SEARCH_API_ID],
            [IntegrationType::EntryApi, self::ENTRY_API_ID],
            [IntegrationType::Widgets, self::WIDGETS_ID],
            [IntegrationType::UiTPAS, self::UITPAS_ID],
        ];
    }
}
