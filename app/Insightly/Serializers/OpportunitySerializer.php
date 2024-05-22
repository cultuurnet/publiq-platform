<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Integrations\Integration;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Pipelines;
use App\Insightly\Serializers\CustomFields\IntegrationTypeSerializer;
use App\Insightly\Serializers\CustomFields\WebsiteSerializer;

final class OpportunitySerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(Integration $integration): array
    {
        $insightlyArray = [
            'OPPORTUNITY_NAME' => $integration->name,
            'OPPORTUNITY_STATE' => OpportunityState::OPEN->value,
            'OPPORTUNITY_DETAILS' => $integration->description,
            'PIPELINE_ID' => $this->pipelines->getOpportunitiesPipelineId(),
            'STAGE_ID' => $this->pipelines->getOpportunityStageId(OpportunityStage::TEST),
            'CUSTOMFIELDS' => [
                (new IntegrationTypeSerializer())->toInsightlyArray($integration->type),
            ],
        ];

        if ($integration->website()) {
            $insightlyArray['CUSTOMFIELDS'][] = (new WebsiteSerializer())->toInsightlyArray($integration->website());
        }

        return $insightlyArray;
    }

    public function toInsightlyArrayForUpdate(Integration $integration, int $insightlyId): array
    {
        $opportunityArray = $this->toInsightlyArray($integration);
        $opportunityArray['OPPORTUNITY_ID'] = $insightlyId;

        return $opportunityArray;
    }
}
