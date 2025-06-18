<?php

declare(strict_types=1);

namespace Tests\Search;

use App\Search\FetchNameForUdb3Organizer;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class FetchNameForUdb3OrganizerTest extends TestCase
{
    use GivenUitpasOrganizers;

    private FetchNameForUdb3Organizer $fetchNameForUdb3Organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fetchNameForUdb3Organizer = new FetchNameForUdb3Organizer();
    }

    public function test_it_fetches_the_name_for_a_udb3_organizer(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $name = 'Test Organizer';

        $result = $this->fetchNameForUdb3Organizer->fetchName($this->givenUitpasOrganizers($organizerId, $name, 1));

        $this->assertEquals($name, $result);
    }

    public function test_it_handles_invalid_organizers(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $result = $this->fetchNameForUdb3Organizer->fetchName($this->givenUitpasOrganizers($organizerId, 'Test Organizer', 0));

        $this->assertEquals('Niet teruggevonden in UDB3', $result);
    }
}
