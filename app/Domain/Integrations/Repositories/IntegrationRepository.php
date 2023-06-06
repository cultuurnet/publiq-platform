<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Integration;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface IntegrationRepository
{
    public function save(Integration $integration): void;
    public function getById(UuidInterface $id): Integration;
    public function deleteById(UuidInterface $id): ?bool;
    public function getByContactEmail(string $email, string $query): Collection;
    public function activateWithCouponCode(UuidInterface $id, string $couponCode): void;
    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId): void;
}
