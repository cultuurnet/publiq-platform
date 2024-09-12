<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Coupons\Models\CouponModel;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Exceptions\InconsistentIntegrationType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\UdbOrganizers;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Pagination\PaginatedCollection;
use App\Pagination\PaginationInfo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationRepository implements IntegrationRepository
{
    public function __construct(
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
        private readonly SubscriptionRepository $subscriptionRepository
    ) {

    }

    public function save(Integration $integration): void
    {
        $this->saveTransaction($integration, null);
    }

    public function saveWithCoupon(Integration $integration, string $couponCode): void
    {
        $this->saveTransaction($integration, $couponCode);
    }

    public function update(Integration $integration): void
    {
        $integrationModel = IntegrationModel::query()->findOrFail($integration->id->toString());
        $integrationModel->update([
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'website' => $integration->website() ? $integration->website()->value : null,
            'subscription_id' => $integration->subscriptionId,
            'status' => $integration->status,
            'partner_status' => $integration->partnerStatus,
            'key_visibility' => $integration->getKeyVisibility(),
            'reminder_email_sent' => $integration->reminderEmailSent,
        ]);
    }

    /** @return Collection<Integration> */
    public function getIntegrationsThatHaveNotBeenActivatedYetByType(IntegrationType $type, int $months): Collection
    {
        return IntegrationModel::query()
            ->where('status', 'draft')
            ->whereNull('reminder_email_sent')
            ->where('created_at', '<', Carbon::now()->subMonths($months))
            ->has('contacts')  // This ensures that only integrations with at least one contact are returned
            ->get()
            ->map(static function (IntegrationModel $integrationModel) {
                return $integrationModel->toDomain();
            });
    }

    public function getById(UuidInterface $id): Integration
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());

        return $integrationModel->toDomain();
    }

    public function getByIdWithTrashed(UuidInterface $id): Integration
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::withTrashed()->findOrFail($id->toString());

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
            ->whereNull('contacts.deleted_at')
            ->when($searchQuery, function (Builder $query, ?string $searchQuery) {
                $query->where('integrations.name', 'like', '%' . $searchQuery . '%');
            })
            ->distinct('integrations.id')
            ->orderBy('integrations.created_at', 'desc')
            ->paginate(10);

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

    public function requestActivation(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode, UdbOrganizers $organizers = null): void
    {
        DB::transaction(function () use ($couponCode, $id, $organizationId, $organizers): void {
            if ($organizers !== null) {
                foreach ($organizers as $organizer) {
                    $this->udbOrganizerRepository->create($organizer);
                }
            }

            if ($couponCode) {
                $this->useCouponOnIntegration($id, $couponCode);
            }

            /** @var IntegrationModel $integrationModel */
            $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
            $integrationModel->requestActivation($organizationId);
        });
    }

    public function activate(UuidInterface $id): void
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
        $integrationModel->activate();
    }

    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode, UdbOrganizers $organizers = null): void
    {
        DB::transaction(function () use ($couponCode, $id, $organizationId, $organizers): void {
            if ($organizers !== null) {
                foreach ($organizers as $organizer) {
                    $this->udbOrganizerRepository->create($organizer);
                }
            }

            if ($couponCode) {
                $this->useCouponOnIntegration($id, $couponCode);
            }

            /** @var IntegrationModel $integrationModel */
            $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
            $integrationModel->activateWithOrganization($organizationId);
        });
    }

    public function approve(UuidInterface $id): void
    {
        /** @var IntegrationModel $integrationModel */
        $integrationModel = IntegrationModel::query()->findOrFail($id->toString());
        $integrationModel->approve();
    }

    private function useCouponOnIntegration(UuidInterface $id, string $couponCode): void
    {
        /** @var CouponModel $couponModel */
        $couponModel = CouponModel::query()
            ->where('code', '=', $couponCode)
            ->whereNull('integration_id')
            ->firstOrFail();
        $couponModel->useOnIntegration($id);
    }

    private function saveTransaction(Integration $integration, ?string $couponCode): void
    {
        DB::transaction(function () use ($integration, $couponCode): void {
            $this->guardIntegrationTypeConsistency($integration->subscriptionId, $integration);

            if ($couponCode) {
                $this->useCouponOnIntegration($integration->id, $couponCode);
            }

            IntegrationModel::query()->create([
                'id' => $integration->id->toString(),
                'type' => $integration->type,
                'name' => $integration->name,
                'description' => $integration->description,
                'subscription_id' => $integration->subscriptionId,
                'status' => $integration->status,
                'partner_status' => $integration->partnerStatus,
                'key_visibility' => $integration->getKeyVisibility(),
                'website' => $integration->website()?->value,
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

            IntegrationCreatedWithContacts::dispatch($integration->id);
        });
    }

    /**
     * @throws InconsistentIntegrationType
     */
    private function guardIntegrationTypeConsistency(UuidInterface $subscriptionId, Integration $integration): void
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        if ($integration->type !== $subscription->integrationType) {
            throw new InconsistentIntegrationType(
                $integration,
                $subscription
            );
        }
    }
}
