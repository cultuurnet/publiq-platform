<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use Ramsey\Uuid\UuidInterface;

interface IntegratioStatusBeforeBlockRepository
{
    public function save(UuidInterface $integrationId, IntegrationStatus $status): void;
    public function getPreviousStatusByIntegrationId(UuidInterface $integrationId): IntegrationStatus;

    public function deleteByIntegrationId(UuidInterface $integrationId): void;
}
