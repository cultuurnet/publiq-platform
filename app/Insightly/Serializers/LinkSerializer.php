<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Contacts\ContactType;

final class LinkSerializer
{
    private const CONTACT_LINK_OBJECT_NAME = 'Contact';
    private const OPPORTUNITY_LINK_OBJECT_NAME = 'Opportunity';

    public function contactToLink(int $contactId, ContactType $contactType): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId,
            'LINK_OBJECT_NAME' => self::CONTACT_LINK_OBJECT_NAME,
            'ROLE' => $contactType === ContactType::Technical ? 'Technisch' : 'Aanvrager',
        ];
    }

    public function opportunityToLink(int $opportunityId): array
    {
        return [
            'LINK_OBJECT_ID' => $opportunityId,
            'LINK_OBJECT_NAME' => self::OPPORTUNITY_LINK_OBJECT_NAME,
        ];
    }
}
