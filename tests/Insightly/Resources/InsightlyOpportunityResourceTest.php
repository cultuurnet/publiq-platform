<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Website;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\Role;
use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyOpportunityResource;
use App\Json;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('provideIntegrationTypes')]
    public function test_it_creates_an_opportunity(IntegrationType $integrationType, array $expectedCustomFields): void
    {
        $name = 'my integration';
        $description = 'description';
        $insightlyId = 42;

        $integration = (new Integration(
            Uuid::uuid4(),
            $integrationType,
            $name,
            $description,
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        ))->withWebsite(new Website('https://www.publiq.be'));

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
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedCreateRequest,
                $expectedResponse,
                $expectedUpdateStageRequest,
                $expectedResponse,
            ));

        $returnedId = $this->resource->create($integration);
        $this->assertEquals($insightlyId, $returnedId);
    }

    public static function provideIntegrationTypes(): Iterator
    {
        yield 'Entry api' => [
            'integrationType' => IntegrationType::EntryApi,
            'expectedCustomFields' => [
                [
                    'FIELD_NAME' => 'Product__c',
                    'CUSTOM_FIELD_ID' => 'Product__c',
                    'FIELD_VALUE' => 'Entry API V3',
                ],
                [
                    'FIELD_NAME' => 'URL_agenda__c',
                    'CUSTOM_FIELD_ID' => 'URL_agenda__c',
                    'FIELD_VALUE' => 'https://www.publiq.be',
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
                [
                    'FIELD_NAME' => 'URL_agenda__c',
                    'CUSTOM_FIELD_ID' => 'URL_agenda__c',
                    'FIELD_VALUE' => 'https://www.publiq.be',
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
                [
                    'FIELD_NAME' => 'URL_agenda__c',
                    'CUSTOM_FIELD_ID' => 'URL_agenda__c',
                    'FIELD_VALUE' => 'https://www.publiq.be',
                ],
            ],
        ];
    }

    public function test_it_gets_an_opportunity(): void
    {
        $insightlyId = 42;
        $opportunity = [
            'OPPORTUNITY_ID' => $insightlyId,
            'OPPORTUNITY_NAME' => 'my integration',
            'OPPORTUNITY_STATE' => OpportunityState::OPEN->value,
            'OPPORTUNITY_DETAILS' => 'description',
            'PIPELINE_ID' => $this->pipelineId,
            'STAGE_ID' => $this->testStageId,
        ];

        $expectedRequest = new Request(
            'GET',
            'Opportunities/' . $insightlyId,
        );

        $expectedResponse = new Response(
            200,
            [],
            Json::encode($opportunity)
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($expectedResponse);

        $actualOpportunity = $this->resource->get($insightlyId);
        $this->assertEquals($opportunity, $actualOpportunity);
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

    public function test_it_updates_the_state_of_an_opportunity(): void
    {
        $opportunityInsightlyId = 42;

        $opportunity = [
            'OPPORTUNITY_NAME' => 'My opportunity',
            'OPPORTUNITY_STATE' => OpportunityState::OPEN->value,
            'OPPORTUNITY_DETAILS' => 'Lorem ipsum',
            'PIPELINE_ID' => $this->pipelineId,
            'STAGE_ID' => $this->offerStageId,
            'CUSTOMFIELDS' => [
                'FIELD_NAME' => 'Product__c',
                'CUSTOM_FIELD_ID' => 'Product__c',
                'FIELD_VALUE' => 'Publicatie Widgets V3',
            ],
        ];

        $expectedGetRequest = new Request(
            'GET',
            'Opportunities/42'
        );
        $expectedGetResponse = new Response(200, [], Json::encode($opportunity));

        $expectedPutRequest = new Request(
            'PUT',
            'Opportunities/',
            [],
            Json::encode([
                'OPPORTUNITY_NAME' => 'My opportunity',
                'OPPORTUNITY_STATE' => OpportunityState::WON->value,
                'OPPORTUNITY_DETAILS' => 'Lorem ipsum',
                'PIPELINE_ID' => $this->pipelineId,
                'STAGE_ID' => $this->offerStageId,
                'CUSTOMFIELDS' => [
                    'FIELD_NAME' => 'Product__c',
                    'CUSTOM_FIELD_ID' => 'Product__c',
                    'FIELD_VALUE' => 'Publicatie Widgets V3',
                ],
            ])
        );
        $expectedPutResponse = new Response(202);

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedGetRequest,
                $expectedGetResponse,
                $expectedPutRequest,
                $expectedPutResponse,
            ));

        $this->resource->updateState($opportunityInsightlyId, OpportunityState::WON);
    }

    #[DataProvider('provideContactTypes')]
    public function test_it_links_a_contact_to_an_opportunity(ContactType $contactType, Role $expectedRole): void
    {
        $expectedRequest = new Request(
            'POST',
            'Opportunities/42/Links',
            [],
            Json::encode([
                'LINK_OBJECT_ID' => 3,
                'LINK_OBJECT_NAME' => 'Contact',
                'ROLE' => $expectedRole->value,
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->linkContact(42, 3, $contactType);
    }

    public static function provideContactTypes(): Iterator
    {
        yield 'Technical' => [
            'contactType' => ContactType::Technical,
            'expectedRole' => Role::Technical,
        ];

        yield 'Functional' => [
            'contactType' => ContactType::Functional,
            'expectedRole' => Role::Applicant,
        ];
    }

    public function test_it_unlinks_a_contact_from_an_opportunity(): void
    {
        $opportunityId = 42;
        $contactId = 53;
        $linkId = 64;

        $opportunityLinks = [
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => $linkId,
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => $contactId,
            ],
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
            [
                'DETAILS' => null,
                'ROLE' => Role::Technical,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
        ];
        $opportunityLinks = Arr::shuffle($opportunityLinks);

        $expectedLinksGetRequest = new Request(
            'GET',
            'Projects/' . $opportunityId . '/Links'
        );
        $expectedLinksGetResponse = new Response(200, [], Json::encode($opportunityLinks));

        $expectedDeleteLinkRequest = new Request(
            'DELETE',
            'Projects/' . $opportunityId . '/Links/' . $linkId
        );
        $expectedDeleteLinkResponse = new Response(202);

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedLinksGetRequest,
                $expectedLinksGetResponse,
                $expectedDeleteLinkRequest,
                $expectedDeleteLinkResponse,
            ));

        $this->resource->unlinkContact($opportunityId, $contactId);
    }

    public function test_it_throws_when_contact_cannot_be_found_in_the_opportunity_links(): void
    {
        $opportunityId = 42;
        $contactId = 53;

        $expectedLinksGetRequest = new Request(
            'GET',
            'Opportunities/42/Links'
        );

        $opportunityLinks = [
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Opportunity',
                'OBJECT_ID' => $opportunityId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
        ];

        $opportunityLinks = Arr::shuffle($opportunityLinks);

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedLinksGetRequest, $actualRequest)))
            ->willReturn(new Response(200, [], Json::encode($opportunityLinks)), );

        $this->expectException(ContactCannotBeUnlinked::class);
        $this->resource->unlinkContact($opportunityId, $contactId);
    }

    public function test_it_updates_the_subscription_of_an_opportunity(): void
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'free',
            'free',
            SubscriptionCategory::Free,
            IntegrationType::SearchApi,
            Currency::EUR,
            3.0,
            null
        );

        $opportunityId = 1;

        $opportunity = [
            'CUSTOMFIELDS' => [
                [
                    'FIELD_NAME' => 'Subscription_plan__c',
                    'CUSTOM_FIELD_ID' => 'Subscription_plan__c',
                    'FIELD_VALUE' => 'Paid',
                ],
                [
                    'FIELD_NAME' => 'Setup_fee__c',
                    'CUSTOM_FIELD_ID' => 'Setup_fee__c',
                    'FIELD_VALUE' => 5.0,
                ],
                [
                    'FIELD_NAME' => 'Subscription_price__c',
                    'CUSTOM_FIELD_ID' => 'Subscription_price__c',
                    'FIELD_VALUE' => 100.0,
                ],
            ],
        ];

        $expectedGetRequest = new Request(
            'GET',
            'Opportunities/' . $opportunityId
        );
        $expectedPutRequest = new Request(
            'PUT',
            'Opportunities/' . $opportunityId,
            [],
            Json::encode([
                'CUSTOMFIELDS' => [
                    [
                        'FIELD_NAME' => 'Subscription_plan__c',
                        'CUSTOM_FIELD_ID' => 'Subscription_plan__c',
                        'FIELD_VALUE' => 'Free',
                    ],
                    [
                        'FIELD_NAME' => 'Setup_fee__c',
                        'CUSTOM_FIELD_ID' => 'Setup_fee__c',
                        'FIELD_VALUE' => null,
                    ],
                    [
                        'FIELD_NAME' => 'Subscription_price__c',
                        'CUSTOM_FIELD_ID' => 'Subscription_price__c',
                        'FIELD_VALUE' => 3.0,
                    ],
                ],
            ])
        );

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedGetRequest,
                new Response(200, [], Json::encode($opportunity)),
                $expectedPutRequest,
                new Response(202),
            ));

        $this->resource->updateSubscription($opportunityId, $subscription, null);
    }
}
