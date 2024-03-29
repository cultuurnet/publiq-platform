<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Listeners;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Listeners\ActivateIntegration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ActivateIntegrationTest extends TestCase
{
    private IntegrationRepository&MockObject $integrationRepository;
    private CouponRepository&MockObject $couponRepository;
    private SubscriptionRepository&MockObject $subscriptionRepository;

    private ActivateIntegration $activateIntegration;

    protected function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);

        $this->activateIntegration = new ActivateIntegration(
            $this->integrationRepository,
            $this->couponRepository,
            $this->subscriptionRepository,
            $this->createMock(LoggerInterface::class)
        );

        parent::setUp();
    }

    #[DataProvider('integrationTypeDataProvider')]
    public function test_it_activates_an_integration(IntegrationType $integrationType): void
    {
        $subscription = $this->givenThereIsASubscription(SubscriptionCategory::Basic);
        $integration = $this->givenThereIsAnIntegration($subscription->id, $integrationType);
        $this->givenThereIsACoupon($integration->id);

        $this->integrationRepository->expects($this->once())
            ->method('activate')
            ->with($integration->id);

        $this->activateIntegration->handle(new IntegrationCreated($integration->id));
    }

    public static function integrationTypeDataProvider(): array
    {
        return [
            [IntegrationType::Widgets],
            [IntegrationType::SearchApi],
        ];
    }

    public function test_it_does_not_activate_without_coupon(): void
    {
        $integration = $this->givenThereIsAnIntegration(Uuid::uuid4(), IntegrationType::SearchApi);
        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integration->id)
            ->willThrowException(new ModelNotFoundException());

        $this->integrationRepository->expects($this->never())
            ->method('activate');

        $this->activateIntegration->handle(new IntegrationCreated($integration->id));
    }

    public function test_it_does_not_activate_for_entry_api(): void
    {
        $integration = $this->givenThereIsAnIntegration(Uuid::uuid4(), IntegrationType::EntryApi);
        $this->givenThereIsACoupon($integration->id);

        $this->integrationRepository->expects($this->never())
            ->method('activate');

        $this->activateIntegration->handle(new IntegrationCreated($integration->id));
    }

    #[DataProvider('subscriptionCategoryProvider')]
    public function test_it_only_activates_for_basic_subscription(SubscriptionCategory $subscriptionCategory): void
    {
        $subscription = $this->givenThereIsASubscription($subscriptionCategory);
        $integration = $this->givenThereIsAnIntegration($subscription->id, IntegrationType::SearchApi);
        $this->givenThereIsACoupon($integration->id);

        $this->integrationRepository->expects($this->never())
            ->method('activate');

        $this->activateIntegration->handle(new IntegrationCreated($integration->id));
    }

    public static function subscriptionCategoryProvider(): array
    {
        return [
            [SubscriptionCategory::Free],
            [SubscriptionCategory::Plus],
            [SubscriptionCategory::Custom],
        ];
    }

    private function givenThereIsAnIntegration(
        UuidInterface $subscriptionId,
        IntegrationType $integrationType
    ): Integration {
        $integration = new Integration(
            Uuid::uuid4(),
            $integrationType,
            'My integration',
            'This is my integration',
            $subscriptionId,
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integration->id)
            ->willReturn($integration);

        return $integration;
    }

    private function givenThereIsASubscription(SubscriptionCategory $subscriptionCategory): Subscription
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan',
            'Basic Plan description',
            $subscriptionCategory,
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

    private function givenThereIsACoupon(UuidInterface $integrationId): void
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
    }
}
