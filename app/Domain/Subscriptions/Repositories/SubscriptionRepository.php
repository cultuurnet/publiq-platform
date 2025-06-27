<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Repositories;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
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

    /**
     * @throws ModelNotFoundException
     */
    public function getByIdWithTrashed(UuidInterface $id): Subscription;

    public function deleteById(UuidInterface $id): ?bool;

    /**
     * @throws ModelNotFoundException
     */
    public function getByIntegrationTypeAndCategory(IntegrationType $integrationType, SubscriptionCategory $category): Subscription;

    /** @return Collection<Subscription> */
    public function all(): Collection;
}
