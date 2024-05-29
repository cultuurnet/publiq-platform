<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Repositories\EloquentIntegrationStatusBeforeBlockRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentIntegrationStatusBeforeBlockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentIntegrationStatusBeforeBlockRepository $integrationStatusBeforeBlockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationStatusBeforeBlockRepository = new EloquentIntegrationStatusBeforeBlockRepository();
    }

    public function test_it_can_save_a_status_before_block(): void
    {
        $integrationId = Uuid::uuid4();
        $status = IntegrationStatus::Draft;

        $this->integrationStatusBeforeBlockRepository->save($integrationId, $status);

        $this->assertDatabaseHas('integration_status_before_block', [
            'integration_id' => $integrationId,
            'status' => $status,
        ]);
    }

    public function test_it_cannot_save_multiple_statuses_for_the_same_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $firstStatus = IntegrationStatus::Draft;

        $this->integrationStatusBeforeBlockRepository->save($integrationId, $firstStatus);
        $this->expectException(UniqueConstraintViolationException::class);
        $this->integrationStatusBeforeBlockRepository->save($integrationId, IntegrationStatus::Active);

        $this->assertDatabaseHas('integration_status_before_block', [
            'integration_id' => $integrationId,
            'status' => $firstStatus,
        ]);
    }

    public function test_it_can_retrieve_a_status(): void
    {
        $integrationId = Uuid::uuid4();
        $firstStatus = IntegrationStatus::PendingApprovalIntegration;

        $this->integrationStatusBeforeBlockRepository->save($integrationId, $firstStatus);

        $this->assertEquals(
            IntegrationStatus::PendingApprovalIntegration,
            $this->integrationStatusBeforeBlockRepository->getPreviousStatusByIntegrationId($integrationId)
        );
    }

    public function test_it_can_delete_a_previous_status(): void
    {
        $integrationId = Uuid::uuid4();
        $status = IntegrationStatus::PendingApprovalIntegration;

        $this->integrationStatusBeforeBlockRepository->save($integrationId, $status);
        $this->integrationStatusBeforeBlockRepository->deleteByIntegrationId($integrationId);

        $this->assertDatabaseMissing('integration_status_before_block', [
            'integration_id' => $integrationId,
            'status' => $status,
        ]);
    }
}
