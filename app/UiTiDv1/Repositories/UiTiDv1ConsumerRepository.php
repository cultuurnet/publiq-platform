<?php

declare(strict_types=1);

namespace App\UiTiDv1\Repositories;

use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface UiTiDv1ConsumerRepository
{
    public function save(UiTiDv1Consumer ...$uitidv1Consumers): void;

    /**
     * @return UiTiDv1Consumer[]
     */
    public function getByIntegrationId(UuidInterface $integrationId): array;

    /**
     * @throws ModelNotFoundException<Model>
     */
    public function getById(UuidInterface $id): UiTiDv1Consumer;

    /**
     * @param array<UuidInterface> $integrationIds
     * @return UiTiDv1Consumer[]
     */
    public function getByIntegrationIds(array $integrationIds): array;

    /**
     * @return UiTiDv1Environment[]
     */
    public function getMissingEnvironmentsByIntegrationId(UuidInterface $integrationId): array;
}
