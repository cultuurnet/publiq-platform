<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\Models\ContactModel;

final class ContactRepository
{
    public function save(Contact $contact): void
    {
        ContactModel::query()->create([
            'id' => $contact->id->toString(),
            'type' => $contact->type,
            'organization' => $contact->organization,
            'first_name' => $contact->firstName,
            'last_name' => $contact->lastName,
            'email' => $contact->email,
        ]);
    }
}
