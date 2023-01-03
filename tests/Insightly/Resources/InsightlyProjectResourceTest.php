<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyProjectResource;
use PHPUnit\Framework\TestCase;
use Tests\AssertRequest;
use Tests\MockInsightlyClient;

final class InsightlyProjectResourceTest extends TestCase
{
    use MockInsightlyClient;
    use AssertRequest;

    private InsightlyProjectResource $resource;

    private int $pipelineId = 1;

    private int $testStageId = 2;

    private int $offerStageId = 3;

    protected function setUp(): void
    {
        $pipelines = new Pipelines([
            'opportunities' => [
                'id' => $this->pipelineId,
                'stages' => [
                    OpportunityStage::TEST->value => $this->testStageId,
                    OpportunityStage::OFFER->value => $this->offerStageId,
                ],
            ],
        ]);
        $this->mockCrmClient($pipelines);

        $this->resource = new InsightlyProjectResource($this->insightlyClient);
    }
}
