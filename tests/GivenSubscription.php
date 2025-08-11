<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait GivenSubscription
{
    protected EloquentSubscriptionRepository $subscriptionRepository;

    private function givenThereIsASubscription(
        ?UuidInterface $id = null,
        ?string $name = null,
        ?string $description = null,
        ?SubscriptionCategory $category = null,
        ?IntegrationType $integrationType = null,
        ?Currency $currency = null,
        ?float $price = null,
        ?float $fee = null
    ): Subscription {
        $subscription = new Subscription(
            $id ?? Uuid::uuid4(),
            $name ?? 'Mock Subscription',
            $description ?? 'Mock description',
            $category ?? SubscriptionCategory::Basic,
            $integrationType ?? IntegrationType::SearchApi,
            $currency ?? Currency::EUR,
            $price ?? 100.0,
            $fee ?? 50.0
        );
        $this->subscriptionRepository->save($subscription);
        return $subscription;
    }
}
