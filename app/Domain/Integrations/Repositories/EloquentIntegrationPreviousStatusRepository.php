<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Models\IntegrationPreviousStatusModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationPreviousStatusRepository implements IntegrationPreviousStatusRepository
{
    public function save(UuidInterface $integrationId, IntegrationStatus $status): void
    {
        IntegrationPreviousStatusModel::query()->create([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $integrationId->toString(),
            'status' => $status->value,
         ]);
    }

    public function deleteByIntegrationId(UuidInterface $integrationId): void
    {
        IntegrationPreviousStatusModel::query()
            ->where('integration_id', '=', $integrationId->toString())
            ->delete();
    }

    public function getPreviousStatusByIntegrationId(UuidInterface $integrationId): IntegrationStatus
    {
        $builder = IntegrationPreviousStatusModel::query()
            ->where('integration_id', '=', $integrationId->toString());

        if ($builder->count() === 0) {
            return IntegrationStatus::Draft;
        }

        /** @var IntegrationPreviousStatusModel $integrationPreviousStatusModel */
        $integrationPreviousStatusModel = $builder->first();

        return IntegrationStatus::from($integrationPreviousStatusModel->status);
    }
}
