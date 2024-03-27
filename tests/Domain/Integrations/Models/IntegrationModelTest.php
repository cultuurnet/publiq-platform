<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Models;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class IntegrationModelTest extends TestCase
{
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
        $this->integrationModel->block();

        Event::assertDispatched(IntegrationBlocked::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Blocked,
        ]);
    }

    public function test_it_handles_activate_with_coupon(): void
    {
        $this->integrationModel->activateWithCoupon();

        Event::assertDispatched(IntegrationActivatedWithCoupon::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_handles_activate_with_organization(): void
    {
        $organizationId = Uuid::uuid4();
        $this->integrationModel->activateWithOrganization($organizationId);

        Event::assertDispatched(IntegrationActivatedWithOrganization::class);

        $this->assertDatabaseHas('integrations', [
            'id' =>  $this->integrationModel->id,
            'status' => IntegrationStatus::Active,
            'organization_id' => $organizationId->toString(),
        ]);
    }
}
