<?php

declare(strict_types=1);

namespace Tests\Insightly\Resources;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Objects\Role;
use App\Insightly\Pipelines;
use App\Insightly\Resources\InsightlyProjectResource;
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

    #[DataProvider('provideIntegrationTypes')]
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
            IntegrationPartnerStatus::THIRD_PARTY,
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

        $expectedResponse = new Response(200, [], Json::encode(['PROJECT_ID' => $insightlyId]));

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(
                self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedCreateRequest, $actualRequest))
            )
            ->willReturn($expectedResponse);

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

    public function test_it_updates_the_the_project_with_a_coupon(): void
    {
        $insightlyId = 41;
        $couponCode = 'coupon123';

        $project = [
            'PROJECT_ID' => $insightlyId,
            'PROJECT_NAME' => 'my integration',
            'STATUS' => ProjectState::NOT_STARTED->value,
            'PROJECT_DETAILS' => 'description',
            'PIPELINE_ID' => $this->pipelineId,
            'STAGE_ID' => $this->testStageId,
        ];

        $projectWithCoupon = $project;
        $projectWithCoupon['CUSTOMFIELDS'][] = [
            'FIELD_NAME' => 'Coupon__c',
            'CUSTOM_FIELD_ID' => 'Coupon__c',
            'FIELD_VALUE' => $couponCode,
        ];

        $expectedGetRequest = new Request(
            'GET',
            'Projects/' . $insightlyId
        );
        $expectedGetResponse = new Response(200, [], Json::encode($project));

        $expectedPutRequest = new Request(
            'PUT',
            'Projects/' . $insightlyId,
            [],
            Json::encode($projectWithCoupon)
        );
        $expectedPutResponse = new Response();

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedGetRequest,
                $expectedGetResponse,
                $expectedPutRequest,
                $expectedPutResponse,
            ));

        $this->resource->updateWithCoupon(42, $couponCode);
    }

    public function test_it_links_an_integration_to_a_project(): void
    {
        $insightlyProjectId = 42;

        $expectedRequest = new Request(
            'POST',
            'Projects/' . $insightlyProjectId . '/Links',
            [],
            Json::encode([
                'LINK_OBJECT_ID' => 31,
                'LINK_OBJECT_NAME' => 'Opportunity',
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->linkOpportunity(42, 31);
    }

    public function test_it_gets_a_project(): void
    {
        $insightlyProjectId = 42;
        $project = [
            'PROJECT_ID' => $insightlyProjectId,
            'PROJECT_NAME' => 'my integration',
            'STATUS' => ProjectState::NOT_STARTED->value,
            'PROJECT_DETAILS' => 'description',
            'PIPELINE_ID' => $this->pipelineId,
            'STAGE_ID' => $this->testStageId,
        ];

        $expectedRequest = new Request(
            'GET',
            'Projects/' . $insightlyProjectId,
        );

        $expectedResponse = new Response(
            200,
            [],
            Json::encode($project)
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)))
            ->willReturn($expectedResponse);

        $actualProject = $this->resource->get($insightlyProjectId);
        $this->assertEquals($project, $actualProject);
    }

    public function test_it_links_a_contact_to_a_project(): void
    {
        $insightlyProjectId = 42;

        $expectedRequest = new Request(
            'POST',
            'Projects/' . $insightlyProjectId . '/Links',
            [],
            Json::encode([
                'LINK_OBJECT_ID' => 20,
                'LINK_OBJECT_NAME' => 'Contact',
            ])
        );

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedRequest, $actualRequest)));

        $this->resource->linkContact($insightlyProjectId, 20);
    }

    public function test_it_deletes_an_project(): void
    {
        $expectedRequest = new Request('DELETE', 'Projects/42');
        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest);

        $this->resource->delete(42);
    }

    public function test_it_unlinks_a_contact_from_a_project(): void
    {
        $projectId = 42;
        $contactId = 53;
        $linkId = 64;

        $projectLinks = [
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => $linkId,
                'OBJECT_NAME' => 'Project',
                'OBJECT_ID' => $projectId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => $contactId,
            ],
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Project',
                'OBJECT_ID' => $projectId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
            [
                'DETAILS' => null,
                'ROLE' => Role::Technical,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Project',
                'OBJECT_ID' => $projectId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
        ];

        $projectLinks = Arr::shuffle($projectLinks);

        $expectedLinksGetRequest = new Request(
            'GET',
            'Projects/' . $projectId . '/Links'
        );
        $expectedLinksGetResponse = new Response(200, [], Json::encode($projectLinks));

        $expectedDeleteLinkRequest = new Request(
            'DELETE',
            'Projects/' . $projectId . '/Links/' . $linkId
        );
        $expectedDeleteLinksResponse = new Response(202);

        $this->insightlyClient->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(self::assertRequestResponseWithCallback(
                $expectedLinksGetRequest,
                $expectedLinksGetResponse,
                $expectedDeleteLinkRequest,
                $expectedDeleteLinksResponse,
            ));

        $this->resource->unlinkContact($projectId, $contactId);
    }

    public function test_it_throws_when_contact_cannot_be_found_in_the_project_links(): void
    {
        $projectId = 42;
        $contactId = 53;

        $expectedLinksGetRequest = new Request(
            'GET',
            'Projects/42/Links'
        );

        $projectLinks = [
            [
                'DETAILS' => null,
                'ROLE' => Role::Applicant,
                'LINK_ID' => random_int(100, 1000),
                'OBJECT_NAME' => 'Project',
                'OBJECT_ID' => $projectId,
                'LINK_OBJECT_NAME' => 'Contact',
                'LINK_OBJECT_ID' => random_int(100, 1000),
            ],
        ];

        $projectLinks = Arr::shuffle($projectLinks);

        $this->insightlyClient->expects($this->once())
            ->method('sendRequest')
            ->with(self::callback(fn ($actualRequest): bool => self::assertRequestIsTheSame($expectedLinksGetRequest, $actualRequest)))
            ->willReturn(new Response(200, [], Json::encode($projectLinks)), );

        $this->expectException(ContactCannotBeUnlinked::class);
        $this->resource->unlinkContact($projectId, $contactId);
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

    public function test_it_updates_the_subscription_of_a_project(): void
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

        $projectId = 1;

        $project = [
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
            'Opportunities/' . $projectId
        );
        $expectedPutRequest = new Request(
            'PUT',
            'Opportunities/' . $projectId,
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
                new Response(200, [], Json::encode($project)),
                $expectedPutRequest,
                new Response(202),
            ));

        $this->resource->updateSubscription($projectId, $subscription, null);
    }
}
