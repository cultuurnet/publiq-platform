<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        });
    }

    public function getById(UuidInterface $id): Integration
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());

        return $integrationModel->toDomain();
    }

    public function getByContactEmail(string $email): Collection
    {
        $integrationModels = IntegrationModel::query()
            ->select('integrations.*')
            ->join('contacts', 'integrations.id', '=', 'contacts.integration_id')
            ->where('contacts.email', $email)
            ->distinct('integrations.id')
            ->orderBy('integrations.created_at')
            ->get();

        $integrations = new Collection();

        foreach ($integrationModels as $integrationModel) {
            $integrations->add($integrationModel->toDomain());
        }

        return $integrations;
    }
}
