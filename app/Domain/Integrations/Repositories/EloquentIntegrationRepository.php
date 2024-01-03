<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Pagination\PaginatedCollection;
use App\Pagination\PaginationInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationRepository implements IntegrationRepository
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
                'status' => $integration->status,
                'partner_status' => $integration->partnerStatus,
            ]);

            foreach ($integration->contacts() as $contact) {
                ContactModel::query()->create([
                    'id' => $contact->id->toString(),
                    'integration_id' => $integration->id->toString(),
                    'type' => $contact->type->value,
                    'first_name' => $contact->firstName,
                    'last_name' => $contact->lastName,
                    'email' => $contact->email,
                ]);
            }
        });
    }

    public function update(Integration $integration): void
    {
        IntegrationModel::query()
            ->findOrFail($integration->id->toString())
            ->update([
                'type' => $integration->type,
                'name' => $integration->name,
                'description' => $integration->description,
                'subscription_id' => $integration->subscriptionId->toString(),
                'status' => $integration->status,
        ]);
    }

    public function getById(UuidInterface $id): Integration
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());

        return $integrationModel->toDomain();
    }

    public function deleteById(UuidInterface $id): ?bool
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
        return $integrationModel->delete();
    }

    public function getByContactEmail(string $email, ?string $searchQuery = null): PaginatedCollection
    {
        $integrationModels = IntegrationModel::query()
            ->select('integrations.*')
            ->join('contacts', 'integrations.id', '=', 'contacts.integration_id')
            ->where('contacts.email', $email)
            ->when($searchQuery, function (Builder $query, ?string $searchQuery) {
                $query->where('integrations.name', 'like', '%' . $searchQuery . '%');
            })
            ->distinct('integrations.id')
            ->orderBy('integrations.created_at', 'desc')
            ->paginate();

        $integrations = new Collection();

        foreach ($integrationModels as $integrationModel) {
            $integrations->add($integrationModel->toDomain());
        }

        $links = array_values($integrationModels->getUrlRange(1, $integrationModels->lastPage()));

        return new PaginatedCollection(
            $integrations,
            new PaginationInfo($links, $integrationModels->total())
        );
    }

    public function activateWithCouponCode(UuidInterface $id, string $couponCode): void
    {
        DB::transaction(static function () use ($couponCode, $id): void {
            /** @var CouponModel $couponModel */
            $couponModel = CouponModel::query()
                ->where('code', '=', $couponCode)
                ->whereNull('integration_id')
                ->firstOrFail();
            $couponModel->useOnIntegration($id);

            /** @var IntegrationModel $integrationModel */
            $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
            $integrationModel->activateWithCoupon();
        });
    }

    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId): void
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
        $integrationModel->activateWithOrganization($organizationId);
    }
}
