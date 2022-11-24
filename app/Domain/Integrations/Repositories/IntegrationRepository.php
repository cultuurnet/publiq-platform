<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class IntegrationRepository
{
    public function save(Integration $integration): void
    {
        DB::transaction(static function () use ($integration): void {
            IntegrationModel::query()->create([
                'id' => $integration->id->toString(),
                'type' => $integration->type,
                'name' => $integration->name,
                'description' => $integration->description,
                'subscription_id' => $integration->subscriptionId,
            ]);

            foreach ($integration->contacts as $contact) {
                ContactModel::query()->create([
                    'id' => $contact->id->toString(),
                    'integration_id' => $integration->id->toString(),
                    'type' => $contact->type,
                    'first_name' => $contact->firstName,
                    'last_name' => $contact->lastName,
                    'email' => $contact->email,
                ]);
            }

            IntegrationCreated::dispatch($integration->id);
        });
    }

    public function getById(UuidInterface $id): Integration
    {
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());

        return new Integration(
            Uuid::fromString($integrationModel->id),
            IntegrationType::from($integrationModel->type),
            $integrationModel->name,
            $integrationModel->description,
            Uuid::fromString($integrationModel->subscription_id),
            []
        );
    }

    public function all(): Collection
    {
        return IntegrationModel::query()->get();
    }
}
