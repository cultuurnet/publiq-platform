<?php

declare(strict_types=1);

namespace Tests\Domain\Subscriptions\Repositories;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentSubscriptionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentSubscriptionRepository $subscriptionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionRepository = new EloquentSubscriptionRepository();
    }

    public function test_it_can_save_a_subscription(): void
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

        $this->subscriptionRepository->save($subscription);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id->toString(),
            'name' => $subscription->name,
            'description' => $subscription->description,
            'category' => $subscription->category,
            'integration_type' => $subscription->integrationType,
            'currency' => $subscription->currency,
            'price' => 1499,
            'fee' => 9999,
        ]);
    }

    public function test_it_can_get_a_subscription(): void
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

        $this->subscriptionRepository->save($subscription);

        $foundSubscription = $this->subscriptionRepository->getById($subscription->id);

        $this->assertEquals($subscription, $foundSubscription);
    }
}
