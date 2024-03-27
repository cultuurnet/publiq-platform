<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Integration;
use App\Pagination\PaginatedCollection;
use Ramsey\Uuid\UuidInterface;

interface IntegrationRepository
{
    public function save(Integration $integration): void;
    public function saveWithCoupon(Integration $integration, string $couponCode): void;
    public function update(Integration $integration): void;
    public function getById(UuidInterface $id): Integration;
    public function deleteById(UuidInterface $id): ?bool;
    public function getByContactEmail(string $email, ?string $searchQuery): PaginatedCollection;
    public function requestActivation(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode): void;
    public function activate(UuidInterface $id): void;
    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode): void;
    public function approve(UuidInterface $id): void;
}
