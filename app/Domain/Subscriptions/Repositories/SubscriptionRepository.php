<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Repositories;

use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface SubscriptionRepository
{
    public function save(Subscription $subscription): void;

    /**
     * @throws ModelNotFoundException
     */
    public function getById(UuidInterface $id): Subscription;

    /** @return Collection<Subscription> */
    public function all(): Collection;
}
