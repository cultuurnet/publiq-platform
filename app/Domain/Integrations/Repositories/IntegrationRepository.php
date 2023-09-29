<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\FormRequests\UpdateIntegration;
use App\Domain\Integrations\Integration;
use App\Pagination\PaginatedCollection;
use Ramsey\Uuid\UuidInterface;

interface IntegrationRepository
{
    public function save(Integration $integration): void;
    public function update(UuidInterface $id, UpdateIntegration $updateIntegration): Integration;
    public function getById(UuidInterface $id): Integration;
    public function deleteById(UuidInterface $id): ?bool;
    public function getByContactEmail(string $email, ?string $searchQuery): PaginatedCollection;
    public function activateWithCouponCode(UuidInterface $id, string $couponCode): void;
    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId): void;
}
