<?php

namespace App\Insightly;

use App\Domain\Contacts\Contact;

interface ContactLink
{
    public function link(Contact $contact): int;
}
