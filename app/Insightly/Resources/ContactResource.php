<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Insightly\Models\InsightlyContact;

interface ContactResource
{
    public function create(Contact $contact): int;

    public function update(Contact $contact, int $id): void;

    public function delete(int $id): void;

    /**
     * @return InsightlyContact[]
     */
    public function findByEmail(string $email): array;
}
