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
use Tests\Keycloak\RealmFactory;

final class IntegrationUrlConverterTest extends TestCase
{
    use CreatesIntegration;

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
            Uuid::uuid4()->toString(),
            'my-secret',
            Environment::Acceptance
        );
    }

    public function test_convert_for_non_first_party(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::THIRD_PARTY]);

        $result = IntegrationUrlConverter::buildLoginUrl($integration, $this->client->environment);
        $this->assertSame('', $result);

        $result = IntegrationUrlConverter::buildCallbackUrls($integration, $this->client->environment);
        $this->assertSame([], $result);

        $result = IntegrationUrlConverter::buildLogoutUrls($integration, $this->client->environment);
        $this->assertSame('', $result);
    }

    public function test_convert_for_first_party_login_url(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::FIRST_PARTY]);
        $integration = $integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login1'),
            // These urls below should NOT be shown! Wrong Realm
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Testing, IntegrationUrlType::Login, 'https://wrong.com/'),
            // You can only have 1 login uri
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Login, 'https://example.com/login2'),
        );

        $this->assertSame(
            'https://example.com/login1',
            IntegrationUrlConverter::buildLoginUrl($integration, $this->client->environment)
        );
    }

    public function test_convert_for_first_party_callback_urls(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::FIRST_PARTY]);
        $integration = $integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Callback, 'https://example.com/callback2'),

            // These urls below should NOT be shown! Wrong Realm
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Production, IntegrationUrlType::Callback, 'https://wrong.com/'),
        );
        $result = IntegrationUrlConverter::buildCallbackUrls($integration, $this->client->environment);

        $this->assertSame([
            'https://example.com/callback1',
            'https://example.com/callback2',
        ], $result);
    }

    public function test_convert_for_first_party_logout_urls(): void
    {
        $integration = $this->givenThereIsAnIntegration($this->integrationId, ['partnerStatus' => IntegrationPartnerStatus::FIRST_PARTY]);
        $integration = $integration->withUrls(
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout1'),
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Acceptance, IntegrationUrlType::Logout, 'https://example.com/logout2'),

            // These urls below should NOT be shown! Wrong Realm
            new IntegrationUrl(Uuid::uuid4(), $integration->id, Environment::Production, IntegrationUrlType::Logout, 'https://wrong.com/'),
        );
        $this->assertSame(
            'https://example.com/logout1#https://example.com/logout2',
            IntegrationUrlConverter::buildLogoutUrls($integration, $this->client->environment)
        );
    }
}
