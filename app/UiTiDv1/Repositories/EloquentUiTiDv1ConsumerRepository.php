<?php

declare(strict_types=1);

namespace App\UiTiDv1\Repositories;

use App\UiTiDv1\Models\UiTiDv1ConsumerModel;

final class EloquentUiTiDv1ConsumerRepository implements UiTiDv1ConsumerRepository
{
    /**
     * @inheritDoc
     */
    public function getByIntegrationIds(array $integrationIds): array
    {
        $ids = array_map(
            fn ($integrationId) => $integrationId->toString(),
            $integrationIds
        );

        return UiTiDv1ConsumerModel::query()
            ->whereIn('integration_id', $ids)
            ->orderBy('created_at')
            ->get()
            ->map(static fn (UiTiDv1ConsumerModel $model) => $model->toDomain())
            ->toArray();
    }
}
