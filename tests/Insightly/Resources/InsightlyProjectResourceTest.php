<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyProjectResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Iterator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;
use Tests\MockInsightlyClient;

final class InsightlyProjectResourceTest extends TestCase
{
    use MockInsightlyClient;
    use AssertRequest;

    private InsightlyProjectResource $resource;

    private int $pipelineId = 1;

    private int $testStageId = 2;

    private int $liveStageId = 3;

    protected function setUp(): void
    {
        $pipelines = new Pipelines([
            'projects' => [
                'id' => $this->pipelineId,
                'stages' => [
                    ProjectStage::TEST->value => $this->testStageId,
                    ProjectStage::LIVE->value => $this->liveStageId,
                ],
            ],
        ]);
        $this->mockCrmClient($pipelines);

        $this->resource = new InsightlyProjectResource($this->insightlyClient);
    }

    /**
     * @dataProvider provideIntegrationTypes
     */
    public function test_it_creates_a_project(IntegrationType $integrationType, array $expectedCustomFields): void
    {
        $name = 'my integration';
        $description = 'description';
        $insightlyId = 42;

        $integration = new Integration(
            Uuid::uuid4(),
            $integrationType,
            $name,
            $description,
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            [],
            collect([])
        );

        $expectedCreateRequest = new Request(
            'POST',
            'Projects/',
            [],
            Json::encode([
                'PROJECT_NAME' => $name,
                'STATUS' => ProjectState::NOT_STARTED->value,
                'PROJECT_DETAILS' => $description,
                'PIPELINE_ID' => $this->pipelineId,
                'STAGE_ID' => $this->testStageId,
                'CUSTOMFIELDS' => $expectedCustomFields,
            ]),
        );

        $expectedUpdateStageRequest = new Request(
            'PUT',
            'Projects/1/Pipeline',
            [],
            Json::encode([
                'PIPELINE_ID' => $this->pipelineId,
                'PIPELINE_STAGE_CHANGE' => [
                    'STAGE_ID' => $this->testStageId,
                ],
            ])
        );

        $expectedResponse = new Response(200, [], Json::encode(['PROJECT_ID' => $insightlyId]));

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

    public function test_it_updates_the_stage_of_the_project(): void
    {
        $expectedRequest = new Request(
            'PUT',
            'Projects/42/Pipeline',
            [],
            Json::encode([
                'PIPELINE_ID' => $this->pipelineId,
                'PIPELINE_STAGE_CHANGE' => [
                    'STAGE_ID' => $this->liveStageId,
                ],
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->updateStage(42, ProjectStage::LIVE);
    }


    public function test_it_deletes_an_project(): void
    {
        $expectedRequest = new Request('DELETE', 'Projects/42');
        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest);

        $this->resource->delete(42);
    }

    public function provideIntegrationTypes(): Iterator
    {
        yield 'Entry api' => [
            'integrationType' => IntegrationType::EntryApi,
            'expectedCustomFields' => [
                [
                    'FIELD_NAME' => 'Product__c',
                    'CUSTOM_FIELD_ID' => 'Product__c',
                    'FIELD_VALUE' => 'Entry API V3',
                ],
            ],
        ];

        yield 'Search api' => [
            'integrationType' => IntegrationType::SearchApi,
            'expectedCustomFields' => [
                [
                    'FIELD_NAME' => 'Product__c',
                    'CUSTOM_FIELD_ID' => 'Product__c',
                    'FIELD_VALUE' => 'Publicatie Search API V3',
                ],
            ],
        ];

        yield 'Widget api' => [
            'integrationType' => IntegrationType::Widgets,
            'expectedCustomFields' => [
                [
                    'FIELD_NAME' => 'Product__c',
                    'CUSTOM_FIELD_ID' => 'Product__c',
                    'FIELD_VALUE' => 'Publicatie Widgets V3',
                ],
            ],
        ];
    }
}
