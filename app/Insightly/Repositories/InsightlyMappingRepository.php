<?php

namespace App\Insightly\Repositories;

use App\Insightly\InsightlyMapping;
use Ramsey\Uuid\UuidInterface;

interface InsightlyMappingRepository
{
    public function save(InsightlyMapping $insightlyMapping): void;

    public function getById(UuidInterface $id): InsightlyMapping;

    public function getByInsightlyId(int $insightlyId): InsightlyMapping;
}
