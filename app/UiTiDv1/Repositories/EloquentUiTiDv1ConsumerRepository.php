<?php

declare(strict_types=1);

namespace App\UiTiDv1\Repositories;

use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1Consumer;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentUiTiDv1ConsumerRepository implements UiTiDv1ConsumerRepository
{
    public function save(UiTiDv1Consumer ...$uitidv1Consumers): void
    {
        if (count($uitidv1Consumers) === 0) {
            return;
        }

        DB::transaction(static function () use ($uitidv1Consumers) {
            foreach ($uitidv1Consumers as $uitidv1Consumer) {
                UiTiDv1ConsumerModel::query()
                    ->updateOrCreate(
                        [
                            'consumer_id' => $uitidv1Consumer->consumerId,
                            'environment' => $uitidv1Consumer->environment->value,
                        ],
                        [
                            'integration_id' => $uitidv1Consumer->integrationId->toString(),
                            'consumer_id' => $uitidv1Consumer->consumerId,
                            'consumer_key' => $uitidv1Consumer->consumerKey,
                            'consumer_secret' => $uitidv1Consumer->consumerSecret,
                            'api_key' => $uitidv1Consumer->apiKey,
                            'environment' => $uitidv1Consumer->environment->value,
                        ]
                    );
            }
        });
    }

    public function getByIntegrationId(UuidInterface $integrationId): array
    {
        return UiTiDv1ConsumerModel::query()
            ->where('integration_id', $integrationId->toString())
            ->get()
            ->map(static fn (UiTiDv1ConsumerModel $model) => $model->toDomain())
            ->toArray();
    }

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
