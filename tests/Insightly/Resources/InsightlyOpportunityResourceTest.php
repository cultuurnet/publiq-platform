<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyOpportunityResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;
use Tests\MockCrmClient;

final class InsightlyOpportunityResourceTest extends TestCase
{
    use MockCrmClient;
    use AssertRequest;

    private InsightlyOpportunityResource $resource;

    private int $pipelineId = 1;

    private int $stageId = 2;

    protected function setUp(): void
    {
        $pipelines = new Pipelines([
            'opportunities' => [
                'id' => $this->pipelineId,
                'stages' => [
                    OpportunityStage::TEST->value => $this->stageId,
                ],
            ],
        ]);
        $this->mockCrmClient($pipelines);

        $this->resource = new InsightlyOpportunityResource($this->insightlyClient);
    }

    public function test_it_creates_an_opportunity(): void
    {
        $name = 'my integration';
        $description = 'description';
        $insightlyId = 42;

        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::EntryApi,
            $name,
            $description,
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );
        $expectedCreateRequest = new Request(
            'POST',
            'Opportunities/',
            [],
            Json::encode([
                'OPPORTUNITY_NAME' => $name,
                'OPPORTUNITY_STATE' => OpportunityState::OPEN->value,
                'OPPORTUNITY_DETAILS' => $description,
                'PIPELINE_ID' => $this->pipelineId,
                'STAGE_ID' => $this->stageId,
                'CUSTOMFIELDS' => [
                    [
                        'FIELD_NAME' => 'Product__c',
                        'CUSTOM_FIELD_ID' => 'Product__c',
                        'FIELD_VALUE' => 'Entry API V3',
                    ],
                ],
            ]),
        );

        $expectedUpdateStageRequest = new Request(
            'PUT',
            'Opportunities/1/Pipeline',
            [],
            Json::encode([
                'PIPELINE_ID' => $this->pipelineId,
                'PIPELINE_STAGE_CHANGE' => [
                    'STAGE_ID' => $this->stageId,
                ],
            ])
        );

        $expectedResponse = new Response(200, [], Json::encode(['OPPORTUNITY_ID' => $insightlyId]));
        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->withConsecutive(
                [self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedCreateRequest, $actualRequest))],
                [self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedUpdateStageRequest, $actualRequest))],
            )
            ->willReturnOnConsecutiveCalls($expectedResponse, $expectedResponse);

        $returnedId = $this->resource->create($integration);
        $this->assertEquals($insightlyId, $returnedId);
    }
}
