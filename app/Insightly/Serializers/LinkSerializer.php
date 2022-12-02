<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

final class LinkSerializer
{
    private const CONTACT_LINK_OBJECT_NAME = 'Contact';

    public function contactToLink(int $contactId): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId,
            'LINK_OBJECT_NAME' => self::CONTACT_LINK_OBJECT_NAME,
            'ROLE' => 'Aanvrager',
        ];
    }
}
