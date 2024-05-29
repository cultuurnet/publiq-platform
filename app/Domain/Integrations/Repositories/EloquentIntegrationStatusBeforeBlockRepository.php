<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Models\IntegrationStatusBeforeBlockModel;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationStatusBeforeBlockRepository implements IntegratioStatusBeforeBlockRepository
{
    public function save(UuidInterface $integrationId, IntegrationStatus $status): void
    {
        IntegrationStatusBeforeBlockModel::query()->create(
            [
            'integration_id' => $integrationId->toString(),
            'status' => $status->value,
         ]
        );
    }

    public function deleteByIntegrationId(UuidInterface $integrationId): void
    {
        IntegrationStatusBeforeBlockModel::query()
            ->where('integration_id', '=', $integrationId->toString())
            ->delete();
    }

    public function getPreviousStatusByIntegrationId(UuidInterface $integrationId): IntegrationStatus
    {
        /** @var IntegrationStatusBeforeBlockModel $integrationStatusBeforeBlockModel */
        $integrationStatusBeforeBlockModel = IntegrationStatusBeforeBlockModel::query()
            ->where('integration_id', '=', $integrationId->toString())
            ->firstOrFail();

        return $integrationStatusBeforeBlockModel->toDomain()->status;
    }
}
