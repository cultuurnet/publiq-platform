<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\Repositories\EloquentIntegrationPreviousStatusRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentIntegrationPreviousStatusRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentIntegrationPreviousStatusRepository $integrationPreviousStatusRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationPreviousStatusRepository = new EloquentIntegrationPreviousStatusRepository();
    }

    public function test_it_can_save_a_previous_status(): void
    {
        $integrationId = Uuid::uuid4();
        $status = IntegrationStatus::Draft;

        $this->integrationPreviousStatusRepository->save($integrationId, $status);

        $this->assertDatabaseHas('integrations_previous_statuses', [
            'integration_id' => $integrationId,
            'status' => $status,
        ]);
    }

    public function test_it_cannot_save_multiple_previous_statuses_for_the_same_integration(): void
    {
        $integrationId = Uuid::uuid4();
        $firstStatus = IntegrationStatus::Draft;

        $this->integrationPreviousStatusRepository->save($integrationId, $firstStatus);
        $this->expectException(UniqueConstraintViolationException::class);
        $this->integrationPreviousStatusRepository->save($integrationId, IntegrationStatus::Active);

        $this->assertDatabaseHas('integrations_previous_statuses', [
            'integration_id' => $integrationId,
            'status' => $firstStatus,
        ]);
    }

    public function test_it_can_retrieve_a_previous_status(): void
    {
        $integrationId = Uuid::uuid4();
        $firstStatus = IntegrationStatus::PendingApprovalIntegration;

        $this->integrationPreviousStatusRepository->save($integrationId, $firstStatus);

        $this->assertEquals(
            IntegrationStatus::PendingApprovalIntegration,
            $this->integrationPreviousStatusRepository->getPreviousStatusByIntegrationId($integrationId)
        );
    }

    public function test_it_can_delete_a_previous_status(): void
    {
        $integrationId = Uuid::uuid4();
        $status = IntegrationStatus::PendingApprovalIntegration;

        $this->integrationPreviousStatusRepository->save($integrationId, $status);
        $this->integrationPreviousStatusRepository->deleteByIntegrationId($integrationId);

        $this->assertDatabaseMissing('integrations_previous_statuses', [
            'integration_id' => $integrationId,
            'status' => $status,
        ]);
    }
}
