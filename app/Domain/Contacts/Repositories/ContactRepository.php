<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ContactRepository
{
    public function save(Contact $contact): void
    {
        ContactModel::query()->create([
            'id' => $contact->id->toString(),
            'integration_id' => $contact->integrationId->toString(),
            'type' => $contact->type,
            'first_name' => $contact->firstName,
            'last_name' => $contact->lastName,
            'email' => $contact->email,
        ]);
    }

    public function getByIntegrationId(UuidInterface $integrationId): Collection
    {
        $contactModels = ContactModel::query()->where('integration_id', $integrationId->toString())->get();

        $contacts = new Collection();

        foreach ($contactModels as $contactModel) {
            $contacts->add(new Contact(
                Uuid::fromString($contactModel->id),
                Uuid::fromString($contactModel->integration_id),
                ContactType::from($contactModel->type),
                $contactModel->first_name,
                $contactModel->last_name,
                $contactModel->email,
            ));
        }

        return $contacts;
    }
}
