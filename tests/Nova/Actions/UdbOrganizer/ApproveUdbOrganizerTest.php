<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Keycloak\Client;
use App\Nova\Actions\UdbOrganizer\ApproveUdbOrganizer;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class ApproveUdbOrganizerTest extends TestCase
{
    use CreatesIntegration;
    use GivenUitpasOrganizers;

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
        $handler = new ApproveUdbOrganizer(
            $udbOrganizerRepository,
            $integrationRepository,
            $api,
            $context
        );

        $id = Uuid::uuid4();
        $integrationId = Uuid::uuid4();
        $organizerId = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
        $clientId = 'keycloak-client-id';

        $udbOrganizer = new UdbOrganizerModel();
        $udbOrganizer->id = $id->toString();
        $udbOrganizer->integration_id = $integrationId->toString();
        $udbOrganizer->organizer_id = $organizerId;
        $udbOrganizer->status = UdbOrganizerStatus::Pending->value;
        $udbOrganizers = new Collection();
        $udbOrganizers->push($udbOrganizer);

        $integration = $this->givenThereIsAnIntegration($integrationId)
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), $clientId, 'client-id-1', Environment::Testing),
            );

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
            )
            ->willReturn(true);

        $udbOrganizerRepository->expects($this->once())
            ->method('updateStatus')
            ->with($udbOrganizer->toDomain(), UdbOrganizerStatus::Approved);

        $handler->handle(
            new ActionFields(collect(), collect()),
            $udbOrganizers
        );
    }
}
