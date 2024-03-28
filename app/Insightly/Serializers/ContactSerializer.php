<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Contacts\Contact;
use App\Insightly\Serializers\CustomFields\ContactTypeSerializer;

final class ContactSerializer
{
    /** @return array<string, string|array<int,array>> */
    public function toInsightlyArray(Contact $contact): array
    {
        return [
            'FIRST_NAME' => $contact->firstName,
            'LAST_NAME' => $contact->lastName,
            'EMAIL_ADDRESS' => $contact->email,
            'CUSTOMFIELDS' => [
                (new ContactTypeSerializer())->toInsightlyArray($contact->type),
            ],
        ];
    }

    /** @return array<string, array<int, array>|int|string> */
    public function toInsightlyArrayForUpdate(Contact $contact, int $insightlyId): array
    {
        $insightlyArray = $this->toInsightlyArray($contact);
        $insightlyArray['CONTACT_ID'] = $insightlyId;

        return $insightlyArray;
    }
}
