<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;

interface ContactResource
{
    public function create(Contact $contact): int;

    public function update(Contact $contact, int $id): void;

    public function delete(int $id): void;

    public function findIdsByEmail(string $email): array;
}
