<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Pipelines;

final class OpportunitySerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(Integration $integration): array
    {
        return [
            'OPPORTUNITY_NAME' => $integration->name,
            'OPPORTUNITY_STATE' => OpportunityState::OPEN->value,
            'OPPORTUNITY_DETAILS' => $integration->description,
            'PIPELINE_ID' => $this->pipelines->getOpportunitiesPipelineId(),
            'STAGE_ID' => $this->pipelines->getOpportunityStageId(OpportunityStage::TEST),
            'CUSTOMFIELDS' => [
                (new IntegrationTypeSerializer())->toInsightlyArray($integration->type),
            ],
        ];
    }
}
