<?php

declare(strict_types=1);

namespace Tests\Domain\Contacts\Models;

use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

final class ContactModelTest extends TestCase
{
    private ContactModel $contactModel;

    private UuidInterface $integrationId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationId = Uuid::uuid4();

        Event::fake([IntegrationCreated::class, ContactCreated::class, ContactDeleted::class]);

        IntegrationModel::query()->create([
            'id' => $this->integrationId->toString(),
            'type' => IntegrationType::EntryApi,
            'name' => 'Test Integration',
            'description' => 'Test Integration description',
            'subscription_id' => Uuid::uuid4()->toString(),
            'status' => IntegrationStatus::Draft,
        ]);

        /** @var ContactModel $contactModel */
        $contactModel = ContactModel::query()->create([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $this->integrationId->toString(),
            'type' => ContactType::Contributor->value,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
        ]);
        $this->contactModel = $contactModel;
    }

    public function test_it_handles_deletes(): void
    {
        $this->contactModel->delete();

        $this->assertSoftDeleted('contacts', [
            'id' => $this->contactModel->id,
            'integration_id' => $this->integrationId->toString(),
            'type' => ContactType::Contributor->value,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
        ]);
    }

    public function test_it_force_deletes_to_resolve_unique_constraints(): void
    {
        $this->contactModel->delete();

        $restoredContact = ContactModel::query()->create([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $this->integrationId->toString(),
            'type' => ContactType::Contributor->value,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $restoredContact->id,
            'integration_id' => $this->integrationId->toString(),
            'type' => ContactType::Contributor->value,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
        ]);
    }
}
