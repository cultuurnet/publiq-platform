<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Repositories;

use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

final class EloquentSubscriptionRepository implements SubscriptionRepository
{
    public function save(Subscription $subscription): void
    {
        SubscriptionModel::query()->updateOrCreate(
            [
                'id' => $subscription->id->toString(),
            ],
            [
            'id' => $subscription->id->toString(),
            'name' => $subscription->name,
            'description' => $subscription->description,
            'category' => $subscription->category,
            'integration_type' => $subscription->integrationType,
            'currency' => $subscription->currency,
            'price' => $subscription->price,
            'fee' => $subscription->fee,
        ]
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getById(UuidInterface $id): Subscription
    {
        /** @var SubscriptionModel $subscription */
        $subscription = SubscriptionModel::query()->findOrFail($id->toString());
        return $subscription->toDomain();
    }

    /** @return Collection<Subscription> */
    public function all(): Collection
    {
        return SubscriptionModel::query()
            ->get()
            ->map(fn (SubscriptionModel $subscriptionModel) => $subscriptionModel->toDomain());
    }
}
