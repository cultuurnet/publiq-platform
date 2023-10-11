<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class CreateOpportunityTest extends TestCase
{
    use MockInsightlyClient;

    private IntegrationRepository&MockObject $integrationRepository;

    private SubscriptionRepository&MockObject $subscriptionRepository;

    private CouponRepository&MockObject $couponRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CreateOpportunity $listener;

    protected function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->mockCrmClient();

        $this->listener = new CreateOpportunity(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->subscriptionRepository,
            $this->couponRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_creates_an_opportunity_with_coupon(): void
    {
        // Given
        $integrationId = Uuid::uuid4();
        $insightlyId = 42;

        $subscription = $this->givenThereIsASubscription();

        $integration = $this->givenThereIsAnIntegration($integrationId, $subscription->id);

        $coupon = $this->givenThereIsACoupon($integrationId);

        // Then it creates the opportunity at Insightly
        $this->opportunityResource->expects($this->once())
            ->method('create')
            ->with($integration)
            ->willReturn($insightlyId);

        // Then it stores the insightlyId mapping
        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $insightlyId,
            ResourceType::Opportunity,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        // Then it updates the subscription inside Insightly
        $this->opportunityResource->expects($this->once())
            ->method('updateSubscription')
            ->with($insightlyId, $subscription, $coupon);

        // When
        $event = new IntegrationCreated($integrationId);
        $this->listener->handle($event);
    }

    public function test_it_creates_an_opportunity_without_coupon(): void
    {
        // Given
        $integrationId = Uuid::uuid4();
        $insightlyId = 42;

        $subscription = $this->givenThereIsASubscription();

        $integration = $this->givenThereIsAnIntegration($integrationId, $subscription->id);

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->willThrowException(new ModelNotFoundException());

        // Then it creates the opportunity at Insightly
        $this->opportunityResource->expects($this->once())
            ->method('create')
            ->with($integration)
            ->willReturn($insightlyId);

        // Then it stores the insightlyId mapping
        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $insightlyId,
            ResourceType::Opportunity,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        // Then it updates the subscription inside Insightly
        $this->opportunityResource->expects($this->once())
            ->method('updateSubscription')
            ->with($insightlyId, $subscription, null);

        // When
        $event = new IntegrationCreated($integrationId);
        $this->listener->handle($event);
    }

    private function givenThereIsACoupon(UuidInterface $integrationId): Coupon
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            false,
            null,
            '12345678901'
        );

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($coupon);

        return $coupon;
    }

    private function givenThereIsAnIntegration(UuidInterface $integrationId, UuidInterface $subscriptionId): Integration
    {
        $integration = new Integration(
            $integrationId,
            IntegrationType::EntryApi,
            'my little integration',
            'a little integration',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        return $integration;
    }

    private function givenThereIsASubscription(): Subscription
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan',
            'Basic Plan description',
            SubscriptionCategory::Basic,
            IntegrationType::SearchApi,
            Currency::EUR,
            14.99,
            99.99
        );

        $this->subscriptionRepository->expects($this->once())
            ->method('getById')
            ->with($subscription->id)
            ->willReturn($subscription);

        return $subscription;
    }
}
