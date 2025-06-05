<?php

declare(strict_types=1);

namespace Tests\Uitpas\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Client;
use App\Keycloak\Realm;
use App\UiTPAS\Listeners\GiveUitpasPermissionsToTestOrganizer;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class GiveUitpasPermissionsToTestOrganizerTest extends TestCase
{
    private IntegrationRepository&MockObject $integrationRepository;
    private UiTPASApiInterface&MockObject $uitpasApi;
    private GiveUitpasPermissionsToTestOrganizer $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->uitpasApi = $this->createMock(UiTPASApiInterface::class);

        $this->listener = new GiveUitpasPermissionsToTestOrganizer(
            $this->integrationRepository,
            $this->uitpasApi,
        );
    }

    public function test_it_adds_permissions_for_integration_created_event(): void
    {
        Config::set(UiTPASConfig::TEST_ORGANISATION->value, 'org-id');

        $integrationId = Uuid::uuid4();
        $clientIdTest = '5f263a50-9474-4690-a962-6935d6f9a3f2';
        $integration = (new Integration(
            Uuid::uuid4(),
            IntegrationType::UiTPAS,
            'My uitpast test',
            'Lorum ipsum',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withKeycloakClients(
            ... [
                new Client(Uuid::uuid4(), Uuid::uuid4(), '5f263a50-9474-4690-a962-6935d6f9a3f2', 'client-test', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-prod', Environment::Production),
            ]
        );
        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->uitpasApi
            ->expects($this->once())
            ->method('addPermissions')
            ->with(
                Realm::getUitIdTestRealm(),
                'org-id',
                $clientIdTest
            );

        $this->listener->handle(new IntegrationCreated($integrationId));
    }

    #[DataProvider('wrongTypes')]
    public function test_it_only_handles_uitpas_types(IntegrationType $type): void
    {
        $integrationId = Uuid::uuid4();
        $integration = (new Integration(
            Uuid::uuid4(),
            $type,
            'My uitpast test',
            'Lorum ipsum',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withKeycloakClients(
            ... [
                new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-test', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-prod', Environment::Production),
            ]
        );

        $this->integrationRepository
            ->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->uitpasApi
            ->expects($this->never())
            ->method('addPermissions');

        $this->listener->handle(new IntegrationCreated($integrationId));
    }

    public static function wrongTypes(): array
    {
        return [
            [IntegrationType::EntryApi],
            [IntegrationType::SearchApi],
            [IntegrationType::Widgets],
        ];
    }
}
