<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Insightly\Resources\ResourceType;

final class LinkSerializer
{
    public function contactToLink(int $contactId): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId,
            'LINK_OBJECT_NAME' => ResourceType::Contact->name,
        ];
    }

    public function contactToContactLink(int $contactId): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId,
            'LINK_OBJECT_NAME' => ResourceType::Contact->name,
            'RELATIONSHIP_ID' => 1,
        ];
    }

    public function opportunityToLink(int $opportunityId): array
    {
        return [
            'LINK_OBJECT_ID' => $opportunityId,
            'LINK_OBJECT_NAME' => ResourceType::Opportunity->name,
        ];
    }

    public function organizationToLink(int $organizationId): array
    {
        return [
            'LINK_OBJECT_ID' => $organizationId,
            'LINK_OBJECT_NAME' => ResourceType::Organization->name,
        ];
    }
}
