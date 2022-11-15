<?php

declare(strict_types=1);

namespace App\Insightly;

use App\Insightly\Objects\OpportunityStage;

final class Pipelines
{
    public function __construct(private readonly array $mapping)
    {
    }

    public function getOpportunitiesPipelineId(): int
    {
        return $this->mapping['opportunities']['id'];
    }

    public function getOpportunityStageId(OpportunityStage $opportunityStage): int
    {
        return $this->mapping['opportunities']['stages'][$opportunityStage->value];
    }
}
