<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Foundation\Http\FormRequest;

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
        ]);

        if (!$this->isAccountingInfoRequired()) {
            $rules->forget(['organization.invoiceEmail', 'organization.vat', 'coupon']);
        }

        return $rules->toArray();
    }

    private function isAccountingInfoRequired(): bool
    {
        /** @var Integration $integration */
        $integration = IntegrationModel::query()
            ->where('id', '=', $this->id)
            ->firstOrFail()
            ->toDomain();

        /** @var Subscription $subscription */
        $subscription = SubscriptionModel::query()
            ->where('id', '=', $integration->subscriptionId->toString())
            ->firstOrFail()
            ->toDomain();

        return $integration->type !== IntegrationType::EntryApi || $subscription->price > 0.0;
    }
}
