<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Models;

use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationModelTest extends TestCase
{
    use RefreshDatabase;

    private IntegrationModel $integrationModel;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->create([
            'id' => Uuid::uuid4()->toString(),
            'type' => IntegrationType::EntryApi,
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'subscription_id' => Uuid::uuid4()->toString(),
            'status' => IntegrationStatus::Draft,
        ]);
        $this->integrationModel = $integrationModel;
    }

    public function test_it_handles_block(): void
    {
        $this->integrationModel->activate();
        $this->integrationModel->block();
        $this->integrationModel->unblock();

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
        ]);
        $this->assertDatabaseMissing('integrations_previous_statuses', [
            'integration_id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_handles_unblock(): void
    {
        $this->integrationModel->block();

        Event::assertDispatched(IntegrationBlocked::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Blocked,
        ]);
        $this->assertDatabaseHas('integrations_previous_statuses', [
            'integration_id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Draft,
        ]);
    }


    public function test_it_handles_request_activation(): void
    {
        $organizationId = Uuid::uuid4();
        $this->integrationModel->requestActivation($organizationId);

        Event::assertDispatched(IntegrationActivationRequested::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::PendingApprovalIntegration,
        ]);
    }

    public function test_it_handles_activate(): void
    {
        $this->integrationModel->activate();

        Event::assertDispatched(IntegrationActivated::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_handles_activate_with_organization(): void
    {
        $organizationId = Uuid::uuid4();
        $this->integrationModel->activateWithOrganization($organizationId);

        Event::assertDispatched(IntegrationActivated::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
        ]);
    }
}
