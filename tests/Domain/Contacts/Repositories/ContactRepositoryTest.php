<?php

declare(strict_types=1);

namespace Tests\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class ContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ContactRepository $contactRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = new ContactRepository();
    }

    public function test_it_can_save_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );

        $this->contactRepository->save($contact);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id->toString(),
            'integration_id' => $contact->integrationId->toString(),
            'type' => $contact->type,
            'first_name' => $contact->firstName,
            'last_name' => $contact->lastName,
            'email' => $contact->email,
        ]);
    }

    public function test_it_can_get_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );

        $this->contactRepository->save($contact);

        $foundContact = $this->contactRepository->getById($contact->id);

        $this->assertEquals($contact, $foundContact);
    }

    public function test_it_can_get_all_contacts_from_an_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $otherIntegrationId = Uuid::uuid4();

        $contactJane = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );
        $this->contactRepository->save($contactJane);

        $contactJohn = new Contact(
            Uuid::uuid4(),
            $integrationId,
            ContactType::Technical,
            'John',
            'Doe',
            'john.doe@anonymous.com'
        );
        $this->contactRepository->save($contactJohn);

        $contactJef = new Contact(
            Uuid::uuid4(),
            $otherIntegrationId,
            ContactType::Technical,
            'Jef',
            'Doe',
            'jef.doe@anonymous.com'
        );
        $this->contactRepository->save($contactJef);

        $foundContacts = $this->contactRepository->getByIntegrationId($integrationId)->toArray();
        usort($foundContacts, static fn (Contact $c1, Contact $c2) => strcmp($c1->firstName, $c2->firstName));

        $this->assertEquals(
            $foundContacts,
            [$contactJane, $contactJohn]
        );
    }
}
