<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Owner;
use App\Domain\Integrations\OwnerId;
use App\Domain\Integrations\OwnerType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private IntegrationRepository $integrationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = new IntegrationRepository();
    }

    public function test_it_can_save_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $subscriptionId = Uuid::uuid4();

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );

        $organizationContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Organization,
            'John',
            'Doe',
            'john.doe@anonymous.com'
        );

        $contributor = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Contributor,
            'Jimmy',
            'Doe',
            'jimmy.doe@anonymous.com'
        );

        $contacts = [$technicalContact, $organizationContact, $contributor];

        $integration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscriptionId,
            $contacts
        );

        $ownerId = new OwnerId('auth0|' . Uuid::uuid4()->toString());

        $owner = new Owner(
            $ownerId,
            $integrationId,
            OwnerType::Integrator
        );

        $this->integrationRepository->save($integration, $owner);

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $subscriptionId,
        ]);

        foreach ($integration->contacts as $contact) {
            $this->assertDatabaseHas('contacts', [
                'id' => $contact->id->toString(),
                'integration_id' => $contact->integrationId->toString(),
                'type' => $contact->type,
                'first_name' => $contact->firstName,
                'last_name' => $contact->lastName,
                'email' => $contact->email,
            ]);
        }

        $this->assertDatabaseHas('owners', [
            'owner_id' => $ownerId->id,
            'integration_id' => $integration->id->toString(),
            'owner_type' => OwnerType::Integrator,
        ]);
    }

    public function test_it_can_get_an_integration_by_id(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            []
        );

        IntegrationModel::query()->insert([
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId,
        ]);

        $integrationFromRepository = $this->integrationRepository->getById($integration->id);

        $this->assertEquals($integration, $integrationFromRepository);
    }

    public function test_it_can_get_integrations_by_owner_id(): void
    {
        $ownerId = new OwnerId('auth0|' . Uuid::uuid4()->toString());

        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            Uuid::uuid4(),
            []
        );

        $otherIntegration = new Integration(
            Uuid::uuid4(),
            IntegrationType::Widgets,
            'Widgets Integration',
            'Widgets Integration description',
            Uuid::uuid4(),
            []
        );

        $owner = new Owner(
            $ownerId,
            $integration->id,
            OwnerType::Integrator
        );

        $this->integrationRepository->save($integration, $owner);
        $this->integrationRepository->save($otherIntegration, $owner);

        $integrations = $this->integrationRepository->getByOwnerId($ownerId);

        $this->assertCount(2, $integrations);
        $this->assertEquals(collect([$integration, $otherIntegration]), $integrations);
    }
}
