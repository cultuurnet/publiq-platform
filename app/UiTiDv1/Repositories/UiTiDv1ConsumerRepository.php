<?php

declare(strict_types=1);

namespace App\UiTiDv1\Repositories;

use App\UiTiDv1\UiTiDv1Consumer;
use Ramsey\Uuid\UuidInterface;

/**
 * uitidv1_consumers is a read-only archive of legacy API keys. Nothing writes to it any more.
 */
interface UiTiDv1ConsumerRepository
{
    /**
     * @param array<UuidInterface> $integrationIds
     * @return UiTiDv1Consumer[]
     */
    public function getByIntegrationIds(array $integrationIds): array;
}
