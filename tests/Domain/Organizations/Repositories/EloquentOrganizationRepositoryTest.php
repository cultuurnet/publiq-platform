<?php

declare(strict_types=1);

namespace Tests\Domain\Organizations\Repositories;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\EloquentOrganizationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\MockUser;
use Tests\TestCase;

final class EloquentOrganizationRepositoryTest extends TestCase
{
    use MockUser;

    use RefreshDatabase;

    private EloquentOrganizationRepository $organizationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createMockUser();

        $this->organizationRepository = new EloquentOrganizationRepository();
    }

    public function test_it_can_save_an_organization(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'facturatie@publiq.be',
            null,
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $this->organizationRepository->save($organization);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id->toString(),
            'name' => $organization->name,
            'invoice_email' => $organization->invoiceEmail,
            'vat' => $organization->vat,
            'street' => $organization->address->street,
            'zip' => $organization->address->zip,
            'city' => $organization->address->city,
            'country' => $organization->address->country,
        ]);
    }

    public function test_it_can_save_an_organization_with_vat(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'BE 0475 250 609',
            'facturatie@publiq.be',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $this->organizationRepository->save($organization);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id->toString(),
            'name' => $organization->name,
            'invoice_email' => $organization->invoiceEmail,
            'vat' => $organization->vat,
            'street' => $organization->address->street,
            'zip' => $organization->address->zip,
            'city' => $organization->address->city,
            'country' => $organization->address->country,
        ]);
    }

    public function test_it_can_get_an_organization(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'facturatie@publiq.be',
            null,
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $this->organizationRepository->save($organization);

        $this->assertEquals($organization, $this->organizationRepository->getById($organization->id));
    }

    public function test_it_can_get_an_organization_with_vat(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'facturatie@publiq.be',
            'BE 0475 250 609',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $this->organizationRepository->save($organization);

        $this->assertEquals($organization, $this->organizationRepository->getById($organization->id));
    }
}
