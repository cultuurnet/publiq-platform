<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Jobs;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Keycloak\Client;
use App\UiTPAS\Jobs\ActivateUiTPASClient;
use App\UiTPAS\Jobs\ActivateUiTPASClientHandler;
use App\UiTPAS\UiTPASApiInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;

final class ActivateUiTPASClientHandlerTest extends TestCase
{
    use CreatesIntegration;

    public function test_it_handles_activate_uitpas_client(): void
    {
        $udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);
        $integrationRepository = $this->createMock(IntegrationRepository::class);
        $api = $this->createMock(UiTPASApiInterface::class);
        $context = new ClientCredentialsContext(
            Environment::Testing,
            'https://test.publiq.be/',
            '123',
            'secret',
            'uitid'
        );
        $handler = new ActivateUiTPASClientHandler(
            $udbOrganizerRepository,
            $integrationRepository,
            $api,
            $context
        );

        $id = Uuid::uuid4();
        $integrationId = Uuid::uuid4();
        $organizerId = 'organizer-123';
        $clientId = 'keycloak-client-id';

        $udbOrganizer = new UdbOrganizer($id, $integrationId, $organizerId);

        $integration = $this->givenThereIsAnIntegration($integrationId)
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), $clientId, 'client-id-1', Environment::Testing),
            );

        $udbOrganizerRepository->expects($this->once())
            ->method('getById')
            ->with($id)
            ->willReturn($udbOrganizer);

        $integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        $api->expects($this->once())
            ->method('addPermissions')
            ->with(
                $context,
                $organizerId,
                $clientId
            );

        $udbOrganizerRepository->expects($this->once())
            ->method('update')
            ->with($this->callback(function ($actual) {
                // check that status is updated to Approved
                return $actual instanceof UdbOrganizer &&
                    $actual->status === UdbOrganizerStatus::Approved;
            }));

        $handler->handle(new ActivateUiTPASClient($id));
    }
}
