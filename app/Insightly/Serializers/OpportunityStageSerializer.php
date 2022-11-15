<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Pipelines;

final class OpportunityStageSerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(OpportunityStage $opportunityStage): array
    {
        return [
            'PIPELINE_ID' => $this->pipelines->getOpportunitiesPipelineId(),
            'PIPELINE_STAGE_CHANGE' => [
                'STAGE_ID' => $this->pipelines->getOpportunityStageId($opportunityStage),
            ],
        ];
    }
}
