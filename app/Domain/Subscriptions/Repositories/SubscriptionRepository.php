<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Repositories;

use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Collection;

final class SubscriptionRepository
{
    public function save(Subscription $subscription): void
    {
        SubscriptionModel::query()->create([
            'id' => $subscription->id->toString(),
            'name' => $subscription->name,
            'description' => $subscription->description,
            'integration_type' => $subscription->integrationType,
            'currency' => $subscription->currency,
            'price' => $subscription->price,
            'billing_interval' => $subscription->billingInterval,
            'fee' => $subscription->fee,
        ]);
    }

    public function all(): Collection
    {
        return SubscriptionModel::query()->get();
    }
}
