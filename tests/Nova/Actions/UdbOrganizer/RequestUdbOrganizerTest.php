<?php

declare(strict_types=1);

namespace Tests\Nova\Actions\UdbOrganizer;

use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Nova\Actions\UdbOrganizer\RequestUdbOrganizer;
use App\Search\Sapi3\SearchService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Nova\Fields\ActionFields;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class RequestUdbOrganizerTest extends TestCase
{
    use GivenUitpasOrganizers;

    private const ORGANIZER_ID = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
    private IntegrationModel $integration;
    private RequestUdbOrganizer $handler;
    private UdbOrganizerRepository&MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new IntegrationModel();
        $this->integration->id = '68498691-4ff0-8010-ae61-c1ece25eaf38';

        $this->repository = $this->createMock(UdbOrganizerRepository::class);
        $searchService = $this->createMock(SearchService::class);
        $searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with(self::ORGANIZER_ID)
            ->willReturn($this->givenUitpasOrganizers($this->integration->id, 'My organisation', 1));

        $this->handler = new RequestUdbOrganizer($this->repository, $searchService);
    }


    public function test_that_it_creates_a_UdbOrganizer(): void
    {
        $this->repository->expects($this->once())
            ->method('create')
            ->with($this->callback(function (UdbOrganizer $organizer) {
                return (string)$organizer->integrationId === $this->integration->id
                    && $organizer->organizerId->toString() === self::ORGANIZER_ID;
            }));

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integration]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . self::ORGANIZER_ID . '" added.', $json['message']);
    }

    public function test_it_handles_duplicates(): void
    {
        $this->repository->expects($this->once())
            ->method('create')
            ->willThrowException(new \PDOException('Db is on fire! Duplicate found', 23000));

        $fields = new ActionFields(collect(['organizer_id' => self::ORGANIZER_ID]), collect());
        $integrations = new Collection([$this->integration]);

        $response = $this->handler->handle($fields, $integrations);

        $json = $response->jsonSerialize();

        $this->assertEquals('Organizer "' . self::ORGANIZER_ID . '" was already added.', $json['danger']);
        Event::assertNotDispatched(UdbOrganizerCreated::class);
    }
}
