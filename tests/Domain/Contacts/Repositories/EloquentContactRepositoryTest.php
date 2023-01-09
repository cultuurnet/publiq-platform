<?php

declare(strict_types=1);

namespace Tests\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\EloquentContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\MockUser;
use Tests\TestCase;

final class EloquentContactRepositoryTest extends TestCase
{
    use MockUser;

    use RefreshDatabase;

    private EloquentContactRepository $contactRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = new EloquentContactRepository();
    }

    public function test_it_can_save_a_contact(): void
    {
        $contact = new Contact(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
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
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
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
            'jane.doe@anonymous.com',
            ContactType::Technical,
            'Jane',
            'Doe'
        );
        $this->contactRepository->save($contactJane);

        $contactJohn = new Contact(
            Uuid::uuid4(),
            $integrationId,
            'john.doe@anonymous.com',
            ContactType::Technical,
            'John',
            'Doe'
        );
        $this->contactRepository->save($contactJohn);

        $contactJef = new Contact(
            Uuid::uuid4(),
            $otherIntegrationId,
            'jef.doe@anonymous.com',
            ContactType::Technical,
            'Jef',
            'Doe'
        );
        $this->contactRepository->save($contactJef);

        $foundContacts = $this->contactRepository->getByIntegrationId($integrationId);

        $this->assertCount(2, $foundContacts);
        $this->assertTrue($foundContacts->contains($contactJane));
        $this->assertTrue($foundContacts->contains($contactJohn));
    }
}
