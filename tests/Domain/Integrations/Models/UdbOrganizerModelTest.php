<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Models;

use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\UiTPAS\Event\UdbOrganizerRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UdbOrganizerModelTest extends TestCase
{
    use RefreshDatabase;

    private string $integrationId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsIntegrator();

        $this->integrationId = Uuid::uuid4()->toString();

        IntegrationModel::query()->create([
            'id' => $this->integrationId,
            'type' => IntegrationType::UiTPAS,
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'subscription_id' => Uuid::uuid4()->toString(),
            'status' => IntegrationStatus::Active,
        ]);
    }

    public function test_it_dispatches_requested_with_the_organizer_id_not_the_row_id(): void
    {
        Event::fake([UdbOrganizerRequested::class]);

        $id = Uuid::uuid4()->toString();
        $organizerId = Uuid::uuid4()->toString();

        UdbOrganizerModel::query()->create([
            'id' => $id,
            'integration_id' => $this->integrationId,
            'organizer_id' => $organizerId,
            'status' => UdbOrganizerStatus::Pending->value,
        ]);

        Event::assertDispatched(
            UdbOrganizerRequested::class,
            fn (UdbOrganizerRequested $event) => $event->udbId->value === $organizerId
                && $event->integrationId->toString() === $this->integrationId
        );
    }

    public function test_it_does_not_dispatch_requested_for_an_approved_organizer(): void
    {
        Event::fake([UdbOrganizerRequested::class]);

        UdbOrganizerModel::query()->create([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $this->integrationId,
            'organizer_id' => Uuid::uuid4()->toString(),
            'status' => UdbOrganizerStatus::Approved->value,
        ]);

        Event::assertNotDispatched(UdbOrganizerRequested::class);
    }
}
