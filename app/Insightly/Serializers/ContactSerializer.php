<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Contacts\Contact;

final class ContactSerializer
{
    /** @return array<string, string> */
    public function toInsightlyArray(Contact $contact): array
    {
        return [
            'FIRST_NAME' => $contact->firstName,
            'LAST_NAME' => $contact->lastName,
            'EMAIL_ADDRESS' => $contact->email,
        ];
    }
}
