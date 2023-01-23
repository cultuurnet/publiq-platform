<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Contact;

interface ContactLink
{
    public function link(Contact $contact): int;
}
