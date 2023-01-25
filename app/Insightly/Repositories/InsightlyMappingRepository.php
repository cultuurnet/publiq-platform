<?php

declare(strict_types=1);

namespace App\Insightly\Repositories;

use App\Insightly\InsightlyMapping;
use App\Insightly\Resources\ResourceType;
use Ramsey\Uuid\UuidInterface;

interface InsightlyMappingRepository
{
    public function save(InsightlyMapping $insightlyMapping): void;

    public function getByIdAndType(UuidInterface $id, ResourceType $type): InsightlyMapping;

    public function getByInsightlyId(int $insightlyId): InsightlyMapping;

    public function deleteById(UuidInterface $id): void;
}
