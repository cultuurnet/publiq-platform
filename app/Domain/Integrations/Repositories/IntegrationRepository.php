<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Models\OwnerModel;
use App\Domain\Integrations\Owner;
use App\Domain\Integrations\OwnerId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class IntegrationRepository
{
    public function save(Integration $integration, Owner $owner): void
    {
        DB::transaction(static function () use ($integration, $owner): void {
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

            OwnerModel::query()->create([
                'owner_id' => $owner->ownerId->id,
                'integration_id' => $integration->id->toString(),
                'owner_type' => $owner->ownerType,
            ]);
        });
    }

    public function getById(UuidInterface $id): Integration
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
        return $this->modelToIntegration($integrationModel);
    }

    public function getByOwnerId(OwnerId $ownerId): Collection
    {
        $ownerModel = OwnerModel::query()->where('owner_id', '=', $ownerId->id)->get();
        return IntegrationModel::query()->whereBelongsTo($ownerModel)->get();
    }

    public function all(): Collection
    {
        return IntegrationModel::query()->get();
    }

    private function modelToIntegration(IntegrationModel $integrationModel): Integration
    {
        return new Integration(
            Uuid::fromString($integrationModel->id),
            IntegrationType::from($integrationModel->type),
            $integrationModel->name,
            $integrationModel->description,
            Uuid::fromString($integrationModel->subscription_id),
            []
        );
    }
}
