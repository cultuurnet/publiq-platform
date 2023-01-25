<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Integrations\Integration;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Pipelines;
use App\Insightly\Serializers\CustomFields\CouponSerializer;
use App\Insightly\Serializers\CustomFields\IntegrationTypeSerializer;

final class ProjectSerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(Integration $integration, string $couponCode = null): array
    {
        $projectAsArray = [
            'PROJECT_NAME' => $integration->name,
            'STATUS' => ProjectState::NOT_STARTED->value,
            'PROJECT_DETAILS' => $integration->description,
            'PIPELINE_ID' => $this->pipelines->getProjectsPipelineId(),
            'STAGE_ID' => $this->pipelines->getProjectStageId(ProjectStage::TEST),
            'CUSTOMFIELDS' => [
                (new IntegrationTypeSerializer())->toInsightlyArray($integration->type),
            ],
        ];

        if ($couponCode !== null) {
            $projectAsArray['CUSTOMFIELDS'][] = (new CouponSerializer())->toInsightlyArray($couponCode);
        }

        return $projectAsArray;
    }
}
