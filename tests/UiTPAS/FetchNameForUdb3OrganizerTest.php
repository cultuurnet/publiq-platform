<?php

declare(strict_types=1);

namespace Tests\UiTPAS;

use App\Search\Sapi3\SearchService;
use App\UiTPAS\FetchNameForUdb3Organizer;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class FetchNameForUdb3OrganizerTest extends TestCase
{
    use GivenUitpasOrganizers;

    private SearchService&MockObject $searchService;

    private FetchNameForUdb3Organizer $fetchNameForUdb3Organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = $this->createMock(SearchService::class);
        $this->fetchNameForUdb3Organizer = new FetchNameForUdb3Organizer($this->searchService);
    }



    public function test_it_fetches_the_name_for_a_udb3_organizer(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $name = 'Test Organizer';

        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with($organizerId)
            ->willReturn($this->givenUitpasOrganizers($organizerId, $name, 1));

        $result = $this->fetchNameForUdb3Organizer->fetchName($organizerId);

        $this->assertEquals($name, $result);
    }

    public function test_it_handles_invalid_organizers(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $name = 'Test Organizer';

        $this->searchService->expects($this->once())
            ->method('findUiTPASOrganizers')
            ->with($organizerId)
            ->willReturn($this->givenUitpasOrganizers($organizerId, $name, 0));

        $result = $this->fetchNameForUdb3Organizer->fetchName($organizerId);

        $this->assertEquals('Niet teruggevonden in UDB3', $result);
    }
}
