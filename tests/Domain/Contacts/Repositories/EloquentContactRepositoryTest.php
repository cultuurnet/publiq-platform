<?php

declare(strict_types=1);

namespace Tests\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Contacts\Repositories\EloquentContactRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentContactRepository $contactRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = new EloquentContactRepository();
    }

    public function test_it_can_create_and_update_a_contact(): void
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

        $updatedContact = new Contact(
            $contact->id,
            Uuid::uuid4(),
            'john.doedoe@anonymous.com',
            ContactType::Functional,
            'John',
            'DoeDoe'
        );
        $this->contactRepository->save($updatedContact);

        $this->assertDatabaseHas('contacts', [
            'id' => $updatedContact->id->toString(),
            'integration_id' => $updatedContact->integrationId->toString(),
            'type' => $updatedContact->type,
            'first_name' => $updatedContact->firstName,
            'last_name' => $updatedContact->lastName,
            'email' => $updatedContact->email,
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

    public function test_it_can_find_deleted_contact_by_id(): void
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

        /** @var ContactModel $contactModel */
        $contactModel = ContactModel::query()->findOrFail($contact->id);
        $contactModel->delete();

        $deletedContact = $this->contactRepository->getDeletedById($contact->id);
        $this->assertEquals($contact, $deletedContact);
    }

    public function test_it_does_not_find_active_contacts_when_finding_deleted(): void
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

        $this->expectException(ModelNotFoundException::class);
        $this->contactRepository->getDeletedById($contact->id);
    }
}
