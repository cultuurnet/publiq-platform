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
        ContactModel::query()->updateOrCreate(
            [
                'id' => $contact->id->toString(),
            ],
            [
                'id' => $contact->id->toString(),
                'integration_id' => $contact->integrationId->toString(),
                'type' => $contact->type->value,
                'first_name' => $contact->firstName,
                'last_name' => $contact->lastName,
                'email' => $contact->email,
            ]
        );
    }
    public function delete(UuidInterface $id): void
    {
        ContactModel::query()->where('id', $id->toString())->delete();
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
        $contactModels = ContactModel::query()->where('integration_id', $integrationId->toString())->orderBy('created_at')->get();

        $contacts = new Collection();

        foreach ($contactModels as $contactModel) {
            $contacts->add($contactModel->toDomain());
        }

        return $contacts;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getByIntegrationIdAndEmail(UuidInterface $integrationId, string $email): Collection
    {
        return ContactModel::query()
            ->where('integration_id', '=', $integrationId)
            ->where('email', '=', $email)
            ->get()
            ->map(fn (ContactModel $contactModel) => $contactModel->toDomain());
    }

    public function getDeletedById(UuidInterface $id): Contact
    {
        /** @var ContactModel $contactModel */
        $contactModel = ContactModel::onlyTrashed()->findOrFail($id);

        return $contactModel->toDomain();
    }

}
