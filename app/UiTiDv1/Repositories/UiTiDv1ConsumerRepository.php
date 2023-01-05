<?php

declare(strict_types=1);

namespace App\UiTiDv1\Repositories;

use App\UiTiDv1\UiTiDv1Consumer;
use Ramsey\Uuid\UuidInterface;

interface UiTiDv1ConsumerRepository
{
    public function save(UiTiDv1Consumer ...$uitidv1Consumers): void;

    /**
     * @return UiTiDv1Consumer[]
     */
    public function getByIntegrationId(UuidInterface $integrationId): array;
}
