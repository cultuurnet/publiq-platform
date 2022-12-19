<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\Models\ContactModel;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

final class EloquentContactRepository implements ContactRepository
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

    public function getById(UuidInterface $id): Contact
    {
        /** @var ContactModel $contactModel */
        $contactModel = ContactModel::query()->findOrFail($id);

        return $contactModel->toDomain();
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getByIntegrationId(UuidInterface $integrationId): Collection
    {
        $contactModels = ContactModel::query()->where('integration_id', $integrationId->toString())->get();

        $contacts = new Collection();

        foreach ($contactModels as $contactModel) {
            $contacts->add($contactModel->toDomain());
        }

        return $contacts;
    }
}
