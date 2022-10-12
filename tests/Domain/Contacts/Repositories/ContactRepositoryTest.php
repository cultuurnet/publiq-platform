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
            ContactType::Technical,
            'Jane',
            'Doe',
            'jane.doe@anonymous.com'
        );

        $this->contactRepository->save($contact);

        $this->assertDatabaseHas('contact', [
            'id' => $contact->id->toString(),
            'type' => $contact->type,
            'first_name' => $contact->firstName,
            'last_name' => $contact->lastName,
            'email' => $contact->email,
        ]);
    }
}
