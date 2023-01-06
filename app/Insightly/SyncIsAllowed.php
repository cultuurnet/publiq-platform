<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;

final class SyncIsAllowed
{
    public static function forContact(Contact $contact): bool
    {
        return match ($contact->type) {
            ContactType::Functional, ContactType::Technical => true,
            ContactType::Contributor => false,
        };
    }
}
