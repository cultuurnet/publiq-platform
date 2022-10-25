<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\BillingInterval;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Subscription;
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

        $this->integrationRepository->save($integration);

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
    }
}
