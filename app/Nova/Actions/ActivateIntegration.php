<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Resources\Organization as OrganizationResource;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

final class ActivateIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private readonly IntegrationRepository $integrationRepository)
    {
    }

    public function handle(ActionFields $fields, Collection $integrations): ActionResponse
    {
        /** @var IntegrationModel $integration */
        $integration = $integrations->first();

        /** @var OrganizationModel $organization */
        $organization = $fields->get('organization');
        $organizationId = Uuid::fromString($organization->id);

        /** @var string $couponCode */
        $couponCode = $fields->get('coupon');

        $this->integrationRepository->activateWithOrganization(
            Uuid::fromString($integration->id),
            $organizationId,
            $couponCode
        );

        return Action::message('Integration "' . $integration->name . '" activated.');
    }

    public function fields(NovaRequest $request): array
    {
        $fields = [
            BelongsTo::make('Organization', 'organization', OrganizationResource::class)
                ->searchable()
                ->withoutTrashed()
                ->rules(
                    'required',
                    'exists:organizations,id'
                ),
        ];
        if (config('app.features.coupons')) {
            $fields[] = Text::make('Coupon', 'coupon')
                ->rules(
                    'nullable',
                    'string',
                    'exists:coupons,code'
                );
        }

        return $fields;
    }
}
