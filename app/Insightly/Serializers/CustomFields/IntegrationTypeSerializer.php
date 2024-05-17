<?php

declare(strict_types=1);

namespace App\Insightly\Serializers\CustomFields;

use App\Domain\Integrations\IntegrationType;

final class IntegrationTypeSerializer
{
    private const CUSTOM_FIELD_INTEGRATION_TYPE = 'Product__c';

    public function toInsightlyArray(IntegrationType $integrationType): array
    {
        return [
            'FIELD_NAME' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
            'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
            'FIELD_VALUE' => $this->integrationTypeToValue($integrationType),
        ];
    }

    private function integrationTypeToValue(IntegrationType $integrationType): string
    {
        return match ($integrationType) {
            IntegrationType::EntryApi => 'Entry API V3',
            IntegrationType::SearchApi => 'Publicatie Search API V3',
            IntegrationType::Widgets => 'Publicatie Widgets V3',
            IntegrationType::UiTPAS => 'UiTPAS API',
        };
    }
}
