<?php

declare(strict_types=1);

namespace Tests\Insightly\Repositories;

use App\Insightly\InsightlyMapping;
use App\Insightly\Models\InsightlyMappingModel;
use App\Insightly\Repositories\EloquentInsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentInsightlyMappingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentInsightlyMappingRepository $insightlyMappingRepository;

    private InsightlyMapping $insightlyMapping;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insightlyMappingRepository = new EloquentInsightlyMappingRepository();

        $this->insightlyMapping = new InsightlyMapping(
            Uuid::uuid4(),
            12800763,
            ResourceType::Opportunity
        );
    }

    public function test_it_can_save_a_mapping(): void
    {
        $this->insightlyMappingRepository->save($this->insightlyMapping);

        $this->assertDatabaseHas(InsightlyMappingModel::class, [
            'id' => $this->insightlyMapping->id,
            'insightly_id' => $this->insightlyMapping->insightlyId,
            'resource_type' => $this->insightlyMapping->resourceType,
        ]);
    }

    public function test_it_can_get_a_mapping_by_id_and_type(): void
    {
        InsightlyMappingModel::query()->insert([
            'id' => $this->insightlyMapping->id,
            'insightly_id' => $this->insightlyMapping->insightlyId,
            'resource_type' => $this->insightlyMapping->resourceType,
        ]);

        $activatedIntegrationInsightlyMapping = new InsightlyMapping(
            $this->insightlyMapping->id,
            12800376,
            ResourceType::Project
        );

        InsightlyMappingModel::query()->insert([
            'id' => $this->insightlyMapping->id,
            'insightly_id' => $activatedIntegrationInsightlyMapping->insightlyId,
            'resource_type' => $activatedIntegrationInsightlyMapping->resourceType,
        ]);

        $foundInsightlyMapping = $this->insightlyMappingRepository->getByIdAndType(
            $this->insightlyMapping->id,
            ResourceType::Opportunity
        );

        $this->assertEquals($this->insightlyMapping, $foundInsightlyMapping);

        $foundInsightlyMappingProject = $this->insightlyMappingRepository->getByIdAndType(
            $this->insightlyMapping->id,
            ResourceType::Project
        );

        $this->assertEquals($activatedIntegrationInsightlyMapping, $foundInsightlyMappingProject);
    }

    public function test_it_can_get_a_mapping_by_insightly_id(): void
    {
        InsightlyMappingModel::query()->insert([
            'id' => $this->insightlyMapping->id,
            'insightly_id' => $this->insightlyMapping->insightlyId,
            'resource_type' => $this->insightlyMapping->resourceType,
        ]);

        $foundInsightlyMapping = $this->insightlyMappingRepository->getByInsightlyId(
            $this->insightlyMapping->insightlyId
        );

        $this->assertEquals($this->insightlyMapping, $foundInsightlyMapping);
    }
}
