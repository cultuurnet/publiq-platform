<?php

declare(strict_types=1);

namespace App\Insightly\Repositories;

use App\Insightly\InsightlyMapping;
use App\Insightly\Models\InsightlyMappingModel;
use App\Insightly\Resources\ResourceType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EloquentInsightlyMappingRepository implements InsightlyMappingRepository
{
    public function save(InsightlyMapping $insightlyMapping): void
    {
        InsightlyMappingModel::query()->create([
            'id' => $insightlyMapping->id,
            'subject_id' => $insightlyMapping->subjectId,
            'insightly_id' => $insightlyMapping->insightlyId,
            'resource_type' => $insightlyMapping->resourceType,
        ]);
    }

    public function getById(UuidInterface $id): InsightlyMapping
    {
        /** @var InsightlyMappingModel $insightlyMappingModel */
        $insightlyMappingModel = InsightlyMappingModel::query()->findOrFail($id);

        return $this->modelToInsightlyMapping($insightlyMappingModel);
    }

    public function getBySubjectId(UuidInterface $subjectId): InsightlyMapping
    {
        /** @var InsightlyMappingModel $insightlyMappingModel */
        $insightlyMappingModel = InsightlyMappingModel::query()->where('subject_id', $subjectId)->firstOrFail();

        return $this->modelToInsightlyMapping($insightlyMappingModel);
    }

    public function getByInsightlyId(int $insightlyId): InsightlyMapping
    {
        /** @var InsightlyMappingModel $insightlyMappingModel */
        $insightlyMappingModel = InsightlyMappingModel::query()->where('insightly_id', $insightlyId)->firstOrFail();

        return $this->modelToInsightlyMapping($insightlyMappingModel);
    }

    public function deleteById(UuidInterface $id): void
    {
        /** @var InsightlyMappingModel $insightlyMappingModel */
        $insightlyMappingModel = InsightlyMappingModel::query()->findOrFail($id);
        $insightlyMappingModel->delete();
    }

    public function deleteBySubjectId(UuidInterface $subjectId): void
    {
        /** @var InsightlyMappingModel $insightlyMappingModel */
        $insightlyMappingModel = InsightlyMappingModel::query()->where('subject_id', $subjectId);
        $insightlyMappingModel->delete();
    }

    private function modelToInsightlyMapping(InsightlyMappingModel $insightlyMappingModel): InsightlyMapping
    {
        return new InsightlyMapping(
            Uuid::fromString($insightlyMappingModel->id),
            Uuid::fromString($insightlyMappingModel->subject_id),
            (int) $insightlyMappingModel->insightly_id,
            ResourceType::from($insightlyMappingModel->resource_type)
        );
    }
}
