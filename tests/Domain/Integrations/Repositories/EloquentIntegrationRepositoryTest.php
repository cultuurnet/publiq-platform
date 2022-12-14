<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentIntegrationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentIntegrationRepository $integrationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = new EloquentIntegrationRepository();
    }

    public function test_it_can_save_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $subscriptionId = Uuid::uuid4();

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $organizationContact = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'john.doe@anonymous.com',
            ContactType::Functional,
            'John',
            'Doe'
        );

        $contributor = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'jimmy.doe@anonymous.com',
            ContactType::Contributor,
            'Jimmy',
            'Doe'
        );

        $contacts = [$technicalContact, $organizationContact, $contributor];

        $integration = new Integration(
            $integrationId,
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscriptionId,
            IntegrationStatus::Draft,
            $contacts
        );

        $this->integrationRepository->save($integration);

        $this->assertDatabaseHas('integrations', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $subscriptionId,
            'status' => $integration->status,
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
    }

    public function test_it_can_get_an_integration_by_id(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        IntegrationModel::query()->insert([
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId,
            'status' => $integration->status,
        ]);

        $integrationFromRepository = $this->integrationRepository->getById($integration->id);

        $this->assertEquals($integration, $integrationFromRepository);
    }

    public function test_it_can_get_integrations_by_contact_email(): void
    {
        $searchIntegrationId = Uuid::uuid4();

        $technicalContact = new Contact(
            Uuid::uuid4(),
            $searchIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $organizationContact = new Contact(
            Uuid::uuid4(),
            $searchIntegrationId,
            'john.doe@anonymous.com',
            ContactType::Functional,
            'John',
            'Doe'
        );

        $searchIntegration = new Integration(
            $searchIntegrationId,
            IntegrationType::SearchApi,
            'Search Integration',
            'Search Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            [$technicalContact, $organizationContact],
        );

        $this->integrationRepository->save($searchIntegration);

        $widgetsIntegrationId = Uuid::uuid4();

        $contributor = new Contact(
            Uuid::uuid4(),
            $widgetsIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Contributor,
            'Jane',
            'Doe',
        );

        $otherTechnicalContact = new Contact(
            Uuid::uuid4(),
            $widgetsIntegrationId,
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe',
        );

        $widgetsIntegration = new Integration(
            $widgetsIntegrationId,
            IntegrationType::Widgets,
            'Widgets Integration',
            'Widgets Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            [$contributor, $otherTechnicalContact]
        );

        $this->integrationRepository->save($widgetsIntegration);

        $foundIntegrations = $this->integrationRepository->getByContactEmail('jane.doe@anonymous.com');

        $this->assertCount(2, $foundIntegrations);
        $this->assertTrue($foundIntegrations->contains($searchIntegration));
        $this->assertTrue($foundIntegrations->contains($widgetsIntegration));
    }

    public function test_it_can_delete_an_integration(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $this->integrationRepository->save($integration);

        $this->integrationRepository->deleteById($integration->id);

        $this->assertSoftDeleted('integrations', [
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscriptionId,
            'status' => IntegrationStatus::Deleted,
        ]);
    }
}
