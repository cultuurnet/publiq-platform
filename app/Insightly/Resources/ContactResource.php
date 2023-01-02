<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\Contact;

interface ContactResource
{
    public function create(Contact $contact): int;

    public function delete(int $id): void;
}
