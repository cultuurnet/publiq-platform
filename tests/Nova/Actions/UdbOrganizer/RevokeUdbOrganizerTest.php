<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Nova\Actions\UdbOrganizer\RevokeUdbOrganizer;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\GivenUitpasOrganizers;

final class RevokeUdbOrganizerTest extends TestCase
{
    use CreatesIntegration;
    use GivenUitpasOrganizers;
    private UdbOrganizerRepository&MockObject $udbOrganizerRepository;
    private IntegrationRepository&MockObject $integrationRepository;
    private UiTPASApiInterface&MockObject $uitpasApi;
    private ClientCredentialsContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->uitpasApi = $this->createMock(UiTPASApiInterface::class);
        $this->context = new ClientCredentialsContext(
            Environment::Production,
            'https://prod.publiq.be/',
            '123',
            'secret',
            'uitid'
        );
    }

    public function test_it_revokes_permissions_and_deletes_udb_organizer(): void
    {
        $organizerId = new UdbUuid(Uuid::uuid4()->toString());
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();

        $udbOrganizer = new UdbOrganizerModel();
        $udbOrganizer->id = Uuid::uuid4()->toString();
        $udbOrganizer->integration_id = $integrationId->toString();
        $udbOrganizer->organizer_id = $organizerId->toString();
        $udbOrganizer->status = UdbOrganizerStatus::Pending->value;
        $udbOrganizers = new Collection();
        $udbOrganizers->push($udbOrganizer);

        $integration = $this->givenThereIsAnIntegration($integrationId)
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), $clientId->toString(), 'secret', Environment::Production),
            );

        $this->udbOrganizerRepository
            ->expects($this->once())
            ->method('delete')
            ->with($integrationId, $organizerId);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId->toString())
            ->willReturn($integration);

        $this->uitpasApi
            ->expects($this->once())
            ->method('deleteAllPermissions')
            ->with($this->context, $organizerId, $clientId);

        $handler = new RevokeUdbOrganizer(
            $this->udbOrganizerRepository,
            $this->integrationRepository,
            $this->uitpasApi,
            $this->context
        );

        $handler->handle(
            new ActionFields(collect(['organizer_id' => $organizerId->toString()]), collect()),
            new Collection([$udbOrganizer])
        );
    }
}
