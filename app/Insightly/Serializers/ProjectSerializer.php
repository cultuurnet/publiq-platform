<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Integrations\Integration;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Pipelines;
use App\Insightly\Serializers\CustomFields\IntegrationTypeSerializer;
use App\Insightly\Serializers\CustomFields\WebsiteSerializer;

final class ProjectSerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(Integration $integration): array
    {
        $insightlyArray = [
            'PROJECT_NAME' => $integration->name,
            'STATUS' => ProjectState::NOT_STARTED->value,
            'PROJECT_DETAILS' => $integration->description,
            'PIPELINE_ID' => $this->pipelines->getProjectsPipelineId(),
            'STAGE_ID' => $this->pipelines->getProjectStageId(ProjectStage::TEST),
            'CUSTOMFIELDS' => [
                (new IntegrationTypeSerializer())->toInsightlyArray($integration->type),
                (new WebsiteSerializer())->toInsightlyArray($integration->website()),
            ],
        ];

        return $insightlyArray;
    }

    public function toInsightlyArrayForUpdate(Integration $integration, int $insightlyId): array
    {
        $projectArray = $this->toInsightlyArray($integration);
        $projectArray['PROJECT_ID'] = $insightlyId;

        return $projectArray;
    }
}
