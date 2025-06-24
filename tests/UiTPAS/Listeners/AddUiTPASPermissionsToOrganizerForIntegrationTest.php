<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\UiTPAS\Listeners\AddUiTPASPermissionsToOrganizerForIntegration;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class AddUiTPASPermissionsToOrganizerForIntegrationTest extends TestCase
{
    private const CLIENT_ID = '5f263a50-9474-4690-a962-6935d6f9a3f2';
    private const INTEGRATION_ID = '373572ec-c7aa-4107-a7f9-403860953b71';

    private IntegrationRepository&MockObject $integrationRepository;
    private KeycloakClientRepository&MockObject $keycloakClientRepository;

    private UiTPASApiInterface&MockObject $uitpasApi;
    private AddUiTPASPermissionsToOrganizerForIntegration $listener;
    private ClientCredentialsContext $testContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);

        $this->keycloakClientRepository
            ->method('getById')
            ->with(Uuid::fromString(self::CLIENT_ID))
            ->willReturn(new Client(Uuid::uuid4(), Uuid::fromString(self::INTEGRATION_ID), self::CLIENT_ID, 'client-test', Environment::Testing));

        $this->uitpasApi = $this->createMock(UiTPASApiInterface::class);

        $this->testContext = new ClientCredentialsContext(
            Environment::Testing,
            'https://account-test.uitid.be/',
            'client-id',
            'client-secret',
            'uitid'
        );
        $this->listener = new AddUiTPASPermissionsToOrganizerForIntegration(
            $this->integrationRepository,
            $this->keycloakClientRepository,
            $this->uitpasApi,
            $this->testContext
        );
    }

    public function test_it_adds_permissions_for_integration_created_event(): void
    {
        Config::set(UiTPASConfig::TEST_ORGANISATION->value, 'd541dbd6-b818-432d-b2be-d51dfc5c0c51');

        $integration = (new Integration(
            Uuid::uuid4(),
            IntegrationType::UiTPAS,
            'My uitpast test',
            'Lorum ipsum',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ));
        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($integration);

        $this->uitpasApi
            ->expects($this->once())
            ->method('addPermissions')
            ->with(
                $this->testContext,
                'd541dbd6-b818-432d-b2be-d51dfc5c0c51',
                self::CLIENT_ID
            );

        $this->listener->handle(new ClientCreated(Uuid::fromString(self::CLIENT_ID)));
    }

    #[dataProvider('wrongTypes')]
    public function test_it_only_handles_uitpas_types(IntegrationType $type): void
    {
        $integration = (new Integration(
            Uuid::uuid4(),
            $type,
            'My uitpast test',
            'Lorum ipsum',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ));
        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($integration);

        $this->uitpasApi
            ->expects($this->never())
            ->method('addPermissions');

        $this->listener->handle(new ClientCreated(Uuid::fromString(self::CLIENT_ID)));
    }

    public static function wrongTypes(): array
    {
        return [
            [IntegrationType::EntryApi],
            [IntegrationType::SearchApi],
            [IntegrationType::Widgets],
        ];
    }

    public function test_it_only_handles_test_clients(): void
    {
        $uuid4 = '5ed9a8a2-4069-4558-8960-f2621ccd71d9';

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository
            ->method('getById')
            ->with($uuid4)
            ->willReturn(new Client(Uuid::uuid4(), Uuid::fromString(self::INTEGRATION_ID), $uuid4, 'client-test', Environment::Production));

        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn((new Integration(
                Uuid::uuid4(),
                IntegrationType::UiTPAS,
                'My uitpast test',
                'Lorum ipsum',
                Uuid::uuid4(),
                IntegrationStatus::Draft,
                IntegrationPartnerStatus::THIRD_PARTY,
            )));

        $this->uitpasApi
            ->expects($this->never())
            ->method('addPermissions');

        $listener = new AddUiTPASPermissionsToOrganizerForIntegration(
            $this->integrationRepository,
            $keycloakClientRepository,
            $this->uitpasApi,
            $this->testContext
        );
        $listener->handle(new ClientCreated(Uuid::fromString($uuid4)));
    }
}
