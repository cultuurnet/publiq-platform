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
    public function getByContactEmail(string $email): Collection;
    public function activateWithCoupon(UuidInterface $id, string $couponCode): void;
}
