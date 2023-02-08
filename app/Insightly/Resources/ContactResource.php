<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;
use App\Insightly\Objects\InsightlyContacts;

interface ContactResource
{
    public function create(Contact $contact): int;

    public function get(int $id): array;

    public function update(Contact $contact, int $id): void;

    public function delete(int $id): void;

    public function findByEmail(string $email): InsightlyContacts;
}
