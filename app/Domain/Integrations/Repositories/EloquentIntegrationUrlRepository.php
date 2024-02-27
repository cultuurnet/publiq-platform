<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationUrlRepository implements IntegrationUrlRepository
{
    public function save(IntegrationUrl $integrationUrl): void
    {
        IntegrationUrlModel::query()->create([
            'id' => $integrationUrl->id->toString(),
            'integration_id' => $integrationUrl->integrationId->toString(),
            'type' => $integrationUrl->type,
            'environment' => $integrationUrl->environment,
            'url' => $integrationUrl->url,
         ]);
    }

    public function getById(UuidInterface $id): IntegrationUrl
    {
        /**
         * @var IntegrationUrlModel $integrationUrlModel
         */
        $integrationUrlModel = IntegrationUrlModel::query()->findOrFail($id->toString());


        return $integrationUrlModel->toDomain();
    }

    public function getByIds(array $ids): array
    {
        $idsAsStrings = array_map(fn ($id) => $id->toString(), $ids);

        return IntegrationUrlModel::query()
           ->whereIn('id', $idsAsStrings)
           ->orderBy('created_at')
           ->get()
           ->map(static fn (IntegrationUrlModel $model) => $model->toDomain())
           ->toArray();
    }

    public function deleteById(UuidInterface $id): ?bool
    {
        /**
         * @var IntegrationUrlModel $integrationUrlModel
         */
        $integrationUrlModel = IntegrationUrlModel::query()->findOrFail($id->toString());
        return $integrationUrlModel->delete();
    }

    /**
     * @param Collection<IntegrationUrl> $integrationUrls
     */
    public function updateUrls(Collection $integrationUrls): void
    {
        if (count($integrationUrls) === 0) {
            return;
        }

        DB::transaction(function () use ($integrationUrls) {
            foreach ($integrationUrls as $integrationUrl) {
                $this->update($integrationUrl);
            }
        });
    }

    public function update(IntegrationUrl $integrationUrl): void
    {
        IntegrationUrlModel::query()
            ->where('id', $integrationUrl->id->toString())
            ->update([
                'integration_id' => $integrationUrl->integrationId->toString(),
                'type' => $integrationUrl->type,
                'environment' => $integrationUrl->environment,
                'url' => $integrationUrl->url,
            ]);
    }
}
