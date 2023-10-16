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

    private InsightlyMapping $opportunityMapping;

    protected function setUp(): void
    {
        parent::setUp();

        $this->insightlyMappingRepository = new EloquentInsightlyMappingRepository();

        $this->opportunityMapping = new InsightlyMapping(
            Uuid::uuid4(),
            12800763,
            ResourceType::Opportunity
        );
    }

    public function test_it_can_save_a_mapping(): void
    {
        $this->insightlyMappingRepository->save($this->opportunityMapping);

        $this->assertDatabaseHas(InsightlyMappingModel::class, [
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $this->opportunityMapping->insightlyId,
            'resource_type' => $this->opportunityMapping->resourceType,
        ]);
    }

    public function test_it_can_delete_a_mapping(): void
    {
        InsightlyMappingModel::query()->insert([
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $this->opportunityMapping->insightlyId,
            'resource_type' => $this->opportunityMapping->resourceType,
        ]);

        $this->insightlyMappingRepository->deleteById($this->opportunityMapping->id);

        $this->assertSoftDeleted(InsightlyMappingModel::class, [
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $this->opportunityMapping->insightlyId,
            'resource_type' => $this->opportunityMapping->resourceType,
        ]);
    }

    public function test_it_can_get_a_mapping_by_id_and_type(): void
    {
        InsightlyMappingModel::query()->insert([
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $this->opportunityMapping->insightlyId,
            'resource_type' => $this->opportunityMapping->resourceType,
        ]);

        $projectMapping = new InsightlyMapping(
            $this->opportunityMapping->id,
            12800376,
            ResourceType::Project
        );

        InsightlyMappingModel::query()->insert([
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $projectMapping->insightlyId,
            'resource_type' => $projectMapping->resourceType,
        ]);

        $foundOpportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
            $this->opportunityMapping->id,
            ResourceType::Opportunity
        );

        $foundProjectMapping = $this->insightlyMappingRepository->getByIdAndType(
            $this->opportunityMapping->id,
            ResourceType::Project
        );

        $this->assertEquals($this->opportunityMapping, $foundOpportunityMapping);
        $this->assertEquals($projectMapping, $foundProjectMapping);
    }

    public function test_it_can_get_a_mapping_by_insightly_id(): void
    {
        InsightlyMappingModel::query()->insert([
            'id' => $this->opportunityMapping->id,
            'insightly_id' => $this->opportunityMapping->insightlyId,
            'resource_type' => $this->opportunityMapping->resourceType,
        ]);

        $foundInsightlyMapping = $this->insightlyMappingRepository->getByInsightlyId(
            $this->opportunityMapping->insightlyId
        );

        $this->assertEquals($this->opportunityMapping, $foundInsightlyMapping);
    }
}
