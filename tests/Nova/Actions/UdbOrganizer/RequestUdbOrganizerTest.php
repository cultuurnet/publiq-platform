<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Api\ClientCredentialsContext;
use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Nova\Actions\UdbOrganizer\RequestUdbOrganizer;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\UiTPASApiInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\CreatesIntegration;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class RequestUdbOrganizerTest extends TestCase
{
    use GivenUitpasOrganizers;
    use CreatesIntegration;

    private const ORGANIZER_ID = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
    private const CLIENT_ID = 'client-id';
    private const INTEGRATION_ID = '68498691-4ff0-8010-ae61-c1ece25eaf38';
    private IntegrationModel $integrationModel;
    private Integration $integration;
    private RequestUdbOrganizer $handler;
    private UdbOrganizerRepository&MockObject $udbOrganizerRepository;

    private IntegrationRepository&MockObject $integrationRepository;
    private UiTPASApiInterface&MockObject $uitpasApi;
    private ClientCredentialsContext $context;
    private SearchService&MockObject $searchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationModel = new IntegrationModel();
        $this->integrationModel->id = self::INTEGRATION_ID;

        $this->integration = $this->givenThereIsAnIntegration(Uuid::fromString(self::INTEGRATION_ID))
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), self::CLIENT_ID, 'secret', Environment::Production),
            );

        $this->udbOrganizerRepository = $this->createMock(UdbOrganizerRepository::class);

        $this->searchService = $this->createMock(SearchService::class);

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->uitpasApi = $this->createMock(UiTPASApiInterface::class);
        $this->context = new ClientCredentialsContext(
            Environment::Production,
            'https://prod.publiq.be/',
            '123',
            'secret',
            'uitid'
        );

        $this->handler = new RequestUdbOrganizer(
            $this->udbOrganizerRepository,
            $this->searchService,
            $this->integrationRepository,
            $this->uitpasApi,
            $this->context,
        );
    }

    public function test_that_it_creates_a_UdbOrganizer(): void
    {
        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->integration);

        $this->uitpasApi->expects($this->once())
            ->method('addPermissions')
            ->with(
                $this->context,
                new UdbUuid(self::ORGANIZER_ID),
                self::CLIENT_ID
            )
            ->willReturn(true);

        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with(self::ORGANIZER_ID)
            ->willReturn($this->givenUitpasOrganizers(self::INTEGRATION_ID, 'My organisation', 1));

        $this->udbOrganizerRepository->expects($this->once())
            ->method('create')
            ->with($this->callback(function (UdbOrganizer $organizer) {
                return (string)$organizer->integrationId === $this->integrationModel->id
                    && $organizer->organizerId->toString() === self::ORGANIZER_ID;
            }));

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integrationModel]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . self::ORGANIZER_ID . '" added.', $json['message']);
    }

    public function test_it_handles_invalid_udb_organisation_id(): void
    {
        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with(self::ORGANIZER_ID)
            ->willReturn($this->givenUitpasOrganizers(self::INTEGRATION_ID, 'My organisation', 0));

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integrationModel]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . self::ORGANIZER_ID . '" not found in UDB3.', $json['danger']);
    }

    public function test_it_handles_duplicates(): void
    {
        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with(self::ORGANIZER_ID)
            ->willReturn($this->givenUitpasOrganizers(self::INTEGRATION_ID, 'My organisation', 1));

        $this->uitpasApi->expects($this->once())
            ->method('addPermissions')
            ->with(
                $this->context,
                new UdbUuid(self::ORGANIZER_ID),
                self::CLIENT_ID
            )
            ->willReturn(true);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->integration);

        $this->udbOrganizerRepository->expects($this->once())
            ->method('create')
            ->willThrowException(new PDOException('Db is on fire! Duplicate found', 23000));

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integrationModel]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . self::ORGANIZER_ID . '" was already added.', $json['danger']);
    }

    public function test_it_handles_failed_uitpas_permissions_call(): void
    {
        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with(self::ORGANIZER_ID)
            ->willReturn($this->givenUitpasOrganizers(self::INTEGRATION_ID, 'My organisation', 1));

        $this->uitpasApi->expects($this->once())
            ->method('addPermissions')
            ->with(
                $this->context,
                new UdbUuid(self::ORGANIZER_ID),
                self::CLIENT_ID
            )
            ->willReturn(false);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->integration);

        $this->udbOrganizerRepository->expects($this->never())
            ->method('create');

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integrationModel]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Failed to set permissions in UiTPAS.', $json['danger']);
    }
}
