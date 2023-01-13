<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Contacts\ContactType;
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
use Illuminate\Support\Arr;
use Iterator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AssertRequest;
use Tests\MockInsightlyClient;

final class InsightlyOpportunityResourceTest extends TestCase
{
    use MockInsightlyClient;
    use AssertRequest;

    private InsightlyOpportunityResource $resource;

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

        $this->resource = new InsightlyOpportunityResource($this->insightlyClient);
    }

    /**
     * @dataProvider provideIntegrationTypes
     */
    public function test_it_creates_an_opportunity(IntegrationType $integrationType, array $expectedCustomFields): void
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
                'STAGE_ID' => $this->testStageId,
                'CUSTOMFIELDS' => $expectedCustomFields,
            ]),
        );

        $expectedUpdateStageRequest = new Request(
            'PUT',
            'Opportunities/1/Pipeline',
            [],
            Json::encode([
                'PIPELINE_ID' => $this->pipelineId,
                'PIPELINE_STAGE_CHANGE' => [
                    'STAGE_ID' => $this->testStageId,
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

    public function test_it_deletes_an_opportunity(): void
    {
        $expectedRequest = new Request('DELETE', 'Opportunities/42');
        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest);

        $this->resource->delete(42);
    }

    public function test_it_updates_the_stage_of_the_opportunity(): void
    {
        $expectedRequest = new Request(
            'PUT',
            'Opportunities/42/Pipeline',
            [],
            Json::encode([
                'PIPELINE_ID' => $this->pipelineId,
                'PIPELINE_STAGE_CHANGE' => [
                    'STAGE_ID' => $this->offerStageId,
                ],
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->updateStage(42, OpportunityStage::OFFER);
    }

    /**
     * @dataProvider provideContactTypes
     */
    public function test_it_links_a_contact_to_an_opportunity(ContactType $contactType, string $expectedRole): void
    {
        $expectedRequest = new Request(
            'POST',
            'Opportunities/42/Links',
            [],
            Json::encode([
                'LINK_OBJECT_ID' => 3,
                'LINK_OBJECT_NAME' => 'Contact',
                'ROLE' => $expectedRole,
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->linkContact(42, 3, $contactType);
    }

    public function provideContactTypes(): Iterator
    {
        yield 'Technical' => [
            'contactType' => ContactType::Technical,
            'expectedRole' => 'Technisch',
        ];

        yield 'Functional' => [
            'contactType' => ContactType::Functional,
            'expectedRole' => 'Aanvrager',
        ];
    }

    public function test_it_unlinks_a_contact_from_an_opportunity(): void
    {
        $opportunityId = 42;
        $contactId = 53;
        $linkId = 64;

        $expectedLinksGetRequest = new Request(
            'GET',
            'Opportunities/42/Links'
        );

        $expectedDeleteLinkRequest = new Request(
            'DELETE',
            'Opportunities/42/Links/64'
        );

        $opportunityLinks = [
            [
                'DETAILS' => null,
                'ROLE' => 'Aanvrager',
                'LINK_ID' => $linkId,
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => $contactId,
            ],
            [
                'DETAILS' => null,
                'ROLE' => 'Aanvrager',
                'LINK_ID' => mt_rand(100, 1000),
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => mt_rand(100, 1000),
            ],
            [
                'DETAILS' => null,
                'ROLE' => 'Technisch',
                'LINK_ID' => mt_rand(100, 1000),
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => mt_rand(100, 1000),
            ],
        ];

        $opportunityLinks = Arr::shuffle($opportunityLinks);

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->withConsecutive(
                [self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedLinksGetRequest, $actualRequest))],
                [self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedDeleteLinkRequest, $actualRequest))],
            )
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], Json::encode($opportunityLinks)),
                new Response(202)
            );

        $this->resource->unlinkContact($opportunityId, $contactId);
    }
}
