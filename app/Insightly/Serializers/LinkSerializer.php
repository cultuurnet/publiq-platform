<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Contacts\ContactType;
use App\Insightly\Objects\Role;
use App\Insightly\Resources\ResourceType;

final class LinkSerializer
{
    public function contactToLink(int $contactId, ContactType $contactType): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId,
            'LINK_OBJECT_NAME' => ResourceType::Contact->name,
            'ROLE' => $this->contactTypeToRole($contactType),
        ];
    }

    public function contactTypeToRole(ContactType $contactType): string
    {
        return $contactType === ContactType::Technical ? Role::Technical->value : Role::Applicant->value;
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
