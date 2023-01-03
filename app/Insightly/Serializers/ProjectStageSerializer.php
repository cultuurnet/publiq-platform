<?php

namespace App\Insightly\Serializers;

use App\Insightly\Objects\ProjectStage;
use App\Insightly\Pipelines;

final class ProjectStageSerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(ProjectStage $projectStage): array
    {
        return [
            'PIPELINE_ID' => $this->pipelines->getProjectsPipelineId(),
            'PIPELINE_STAGE_CHANGE' => [
                'STAGE_ID' => $this->pipelines->getProjectStageId($projectStage)
            ],
        ];
    }
}
