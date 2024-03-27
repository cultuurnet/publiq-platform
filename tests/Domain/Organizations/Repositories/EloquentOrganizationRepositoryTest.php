<?php

declare(strict_types=1);

namespace Tests\Domain\Organizations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\EloquentOrganizationRepository;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentOrganizationRepositoryTest extends TestCase
{
    private EloquentOrganizationRepository $organizationRepository;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_it_can_get_an_organization_based_on_integration_id(): void
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

        $integrationId = Uuid::uuid4();
        IntegrationModel::query()->create([
            'id' => $integrationId,
            'type' => IntegrationType::EntryApi,
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'subscription_id' => Uuid::uuid4(),
            'status' => IntegrationStatus::Draft,
            'organization_id' => $organization->id,
        ]);

        $this->assertEquals($organization, $this->organizationRepository->getByIntegrationId($integrationId));
    }
}
