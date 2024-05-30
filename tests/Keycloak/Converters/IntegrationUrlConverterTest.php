<?php

declare(strict_types=1);

namespace Tests\Keycloak\Converters;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Keycloak\Client;
use App\Keycloak\Converters\IntegrationUrlConverter;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreatesIntegration;
use Tests\Keycloak\ConfigFactory;
use Tests\Keycloak\RealmFactory;

final class IntegrationUrlConverterTest extends TestCase
{
    use CreatesIntegration;
    use ConfigFactory;
    use RealmFactory;

    private Client $client;
    private UuidInterface $integrationId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationId = Uuid::uuid4();
        $this->client = new Client(
            Uuid::uuid4(),
            $this->integrationId,
            Uuid::uuid4(),
            'my-secret',
            $this->givenAcceptanceRealm()
        );
    }

    public function test_convert_for_non_first_party(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::THIRD_PARTY]);

        $result = IntegrationUrlConverter::convert($integration, $this->client);

        $expected = [
            'baseUrl' => '',
            'redirectUris' => [],
            'attributes' => ['post.logout.redirect.uris' => ''],
            'webOrigins' => ['+'],
        ];

        $this->assertSame($expected, $result);
    }

    public function test_convert_for_first_party(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::FIRST_PARTY]);
        $integration = $integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login2'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout2'),

            // These urls below should NOT be shown!
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Production, IntegrationUrlType::Logout, 'https://wrong.com/'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Testing, IntegrationUrlType::Login, 'https://wrong.com/'),
            // You can only have 1 callback uri
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://wrong.com/')
        );
        $result = IntegrationUrlConverter::convert($integration, $this->client);

        $expected = [
            'baseUrl' => 'https://example.com/callback',
            'redirectUris' => [
                'https://example.com/login1',
                'https://example.com/login2',
            ],
            'attributes' => ['post.logout.redirect.uris' => 'https://example.com/logout1#https://example.com/logout2'],
            'webOrigins' => ['+'],
        ];

        $this->assertSame($expected, $result);
    }
}
