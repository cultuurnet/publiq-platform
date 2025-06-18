<?php

declare(strict_types=1);

namespace Tests\Search;

use App\Search\UdbOrganizerNameResolver;
use Tests\GivenUitpasOrganizers;
use Tests\TestCase;

final class UdbOrganizerNameResolverTest extends TestCase
{
    use GivenUitpasOrganizers;

    private UdbOrganizerNameResolver $udbOrganizerNameResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->udbOrganizerNameResolver = new UdbOrganizerNameResolver();
    }

    public function test_it_fetches_the_name_for_a_udb3_organizer(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $name = 'Test Organizer';

        $result = $this->udbOrganizerNameResolver->getName($this->givenUitpasOrganizers($organizerId, $name, 1));

        $this->assertEquals($name, $result);
    }

    public function test_it_handles_invalid_organizers(): void
    {
        $organizerId = 'b4530a99-86e9-44bc-a492-2aa6fa8f74a0';
        $result = $this->udbOrganizerNameResolver->getName($this->givenUitpasOrganizers($organizerId, 'Test Organizer', 0));

        $this->assertNull($result);
    }
}
