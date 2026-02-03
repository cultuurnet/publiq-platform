<?php

declare(strict_types=1);

namespace Tests\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\UiTPAS\SynchronizeUiTPASPermissionsHandler;
use App\UiTPAS\UiTPASApiInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreateIntegration;

final class SynchronizeUiTPASPermissionsHandlerTest extends TestCase
{
    use CreateIntegration;

    public const DEMO_ORG_ID = '4006f2a8-f9e9-40b6-b3c3-7dbeb621eda1';
    private ClientCredentialsContext $contextTest;
    private ClientCredentialsContext $contextProd;
    private SynchronizeUiTPASPermissionsHandler $handler;
    private UiTPASApiInterface&MockObject $UiTPASApiInterface;

    public function setUp(): void
    {
        $this->contextTest = new ClientCredentialsContext(
            Environment::Testing,
            'https://test.publiq.be/',
            '123',
            'secret',
            'uitid'
        );

        $this->contextProd = new ClientCredentialsContext(
            Environment::Production,
            'https://publiq.be/',
            '456',
            'geheimpje',
            'uitid'
        );

        $this->UiTPASApiInterface = $this->createMock(UiTPASApiInterface::class);

        $this->handler = new SynchronizeUiTPASPermissionsHandler(
            $this->contextTest,
            new UdbUuid(self::DEMO_ORG_ID),
            $this->contextProd,
            $this->UiTPASApiInterface,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testCanSyncUiTPASOrganizers(): void
    {
        $this->UiTPASApiInterface->expects($this->exactly(2))
            ->method('updatePermissions')
            ->willReturn(true);

        $integration = $this->givenThereIsAnIntegration(
            Uuid::uuid4(),
            [
                'type' => IntegrationType::UiTPAS,
                'status' => IntegrationStatus::Active,
            ]
        )->withKeycloakClients(
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Testing),
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Production)
        )->withUdbOrganizers(
            new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::uuid4(),
                new UdbUuid(Uuid::uuid4()->toString()),
                UdbOrganizerStatus::Approved,
                Uuid::uuid4()
            ),
            // Pending organizer should be skipped
            new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::uuid4(),
                new UdbUuid(Uuid::uuid4()->toString()),
                UdbOrganizerStatus::Pending,
                Uuid::uuid4()
            )
        );

        $result = $this->handler->handle($integration);

        $this->assertTrue($result->success);
    }


    public function testGivesErrorWithFailedSync(): void
    {
        $this->UiTPASApiInterface->expects($this->exactly(3))
            ->method('updatePermissions')
            ->willReturn(false);

        $orgId = Uuid::uuid4()->toString();
        $orgId2 = Uuid::uuid4()->toString();

        $integration = $this->givenThereIsAnIntegration(
            Uuid::uuid4(),
            [
                'type' => IntegrationType::UiTPAS,
                'status' => IntegrationStatus::Active,
            ]
        )->withKeycloakClients(
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Testing),
            new Client(Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()->toString(), 'client-id-1', Environment::Production)
        )->withUdbOrganizers(
            new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::uuid4(),
                new UdbUuid($orgId),
                UdbOrganizerStatus::Approved,
                Uuid::uuid4()
            ),
            new UdbOrganizer(
                Uuid::uuid4(),
                Uuid::uuid4(),
                new UdbUuid($orgId2),
                UdbOrganizerStatus::Approved,
                Uuid::uuid4()
            )
        );

        $result = $this->handler->handle($integration);

        $this->assertFalse($result->success);
        $this->assertEquals([new UdbUuid($orgId), new UdbUuid($orgId2)], $result->failedOrganizerIds);
    }
}
