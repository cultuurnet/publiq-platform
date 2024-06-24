<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 */
final class RequestActivationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = collect([
            ...(new CreateOrganizationRequest())->rules(),
            'coupon' => ['nullable', 'string', 'max:255'],
            'organizers' => ['required','array'],
            'organizers.*.name' => ['required', 'string'],
            'organizers.*.id' => ['required', 'string'],
        ]);

        if (!$this->isAccountingInfoRequired() || $this->isUITPAS()) {
            $rules->forget(['organization.invoiceEmail', 'organization.vat', 'coupon']);
        }

        if (!$this->isUITPAS()) {
            $rules->forget(['organizers']);
        }

        return $rules->toArray();
    }

    private function isAccountingInfoRequired(): bool
    {
        /** @var IntegrationRepository $integrationRepository */
        $integrationRepository = App::get(IntegrationRepository::class);
        $integration = $integrationRepository->getById(Uuid::fromString($this->id));

        /** @var SubscriptionRepository $subscriptionRepository */
        $subscriptionRepository = App::get(SubscriptionRepository::class);
        $subscription = $subscriptionRepository->getById($integration->subscriptionId);

        return $integration->type !== IntegrationType::EntryApi || $subscription->price > 0.0;
    }

    private function isUITPAS(): bool
    {
        /** @var IntegrationRepository $integrationRepository */
        $integrationRepository = App::get(IntegrationRepository::class);
        $integration = $integrationRepository->getById(Uuid::fromString($this->id));
        return $integration->type === IntegrationType::UiTPAS;
    }
}
