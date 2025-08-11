<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\UiTPAS\Event\UdbOrganizerDeleted;
use App\UiTPAS\Listeners\RevokeUiTPASPermissions;
use App\UiTPAS\UiTPASApiInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\CreateIntegration;

final class RevokeUiTPASPermissionsTest extends TestCase
{
    use CreateIntegration;
    private IntegrationRepository&MockObject $integrationRepository;
    private UiTPASApiInterface&MockObject $UiTPASApi;
    private ClientCredentialsContext $prodContext;
    private LoggerInterface&MockObject $logger;

    public function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->UiTPASApi = $this->createMock(UiTPASApiInterface::class);
        $this->prodContext = new ClientCredentialsContext(
            Environment::Production,
            'https://account-prod.uitid.be/',
            'client-id',
            'client-secret',
            'uitid'
        );
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_it_revokes_permissions_successfully(): void
    {
        $udbId = new UdbUuid('68889beb-06b8-8321-a425-f027f3f50c90');
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), $integrationId, $clientId->toString(), 'secret', Environment::Production),
            );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $this->UiTPASApi->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->prodContext,
                $udbId,
                $clientId
            )
            ->willReturn(true);

        $listener = new RevokeUiTPASPermissions(
            $this->integrationRepository,
            $this->UiTPASApi,
            $this->prodContext,
            $this->logger
        );

        $listener->handle(new UdbOrganizerDeleted($udbId, $integrationId));
    }
}
