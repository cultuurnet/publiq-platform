<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

use App\Domain\Contacts\ContactType;

final class ContactTypeSerializer
{
    public const CUSTOM_FIELD_RELATION_TYPE = 'Relatie_contact__c';

    public function toInsightlyArray(ContactType $contactType): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_RELATION_TYPE,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_RELATION_TYPE,
            'FIELD_VALUE' => $this->contactTypeToRelationType($contactType),
        ];
    }

    private function contactTypeToRelationType(ContactType $contactType): string
    {
        return match ($contactType) {
            ContactType::Functional => 'Functioneel contact publiq platform',
            ContactType::Technical => 'Technisch contact publiq platform',
            ContactType::Contributor => 'Unsupported contact type',
        };
    }
}
