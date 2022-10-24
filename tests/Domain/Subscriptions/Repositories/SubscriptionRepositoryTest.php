<?php

declare(strict_types=1);

namespace Tests\Domain\Subscriptions\Repositories;

use App\Domain\Subscriptions\BillingInterval;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class SubscriptionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionRepository $subscriptionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionRepository = new SubscriptionRepository();
    }

    public function test_it_can_save_a_subscription(): void
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan',
            'Basic Plan description',
            Currency::EUR,
            999,
            BillingInterval::Monthly,
            1499
        );

        $this->subscriptionRepository->save($subscription);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id->toString(),
            'name' => $subscription->name,
            'description' => $subscription->description,
            'currency' => $subscription->currency,
            'price' => $subscription->price,
            'billing_interval' => $subscription->billingInterval,
            'fee' => $subscription->fee,
        ]);
    }
}
