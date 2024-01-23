<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Contact;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface ContactRepository
{
    public function save(Contact $contact): void;

    public function delete(UuidInterface $id): void;

    public function getById(UuidInterface $id): Contact;

    public function getDeletedById(UuidInterface $id): Contact;

    /**
     * @return Collection<int, Contact>
     */
    public function getByIntegrationId(UuidInterface $integrationId): Collection;

    /**
     * @return Collection<int, Contact>
     */
    public function getByIntegrationIdAndEmail(UuidInterface $integrationId, string $email): Collection;
}
