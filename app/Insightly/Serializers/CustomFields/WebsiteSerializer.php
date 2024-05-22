<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

use App\Domain\Integrations\Website;

final class WebsiteSerializer
{
    public const CUSTOM_FIELD_WEBSITE = 'URL_agenda__c';

    public function toInsightlyArray(?Website $website): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_WEBSITE,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_WEBSITE,
            'FIELD_VALUE' => $website ? $website->value : '-',
        ];
    }
}
