<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

final class ActivateIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public readonly IntegrationRepository $repository)
    {
    }

    public function handle(ActionFields $fields, Collection $integrations): array
    {
        /** @var string $couponCode */
        $couponCode = $fields->get('coupon');

        try {
            /** @var IntegrationModel $integration */
            $integration = $integrations->first();
            $this->repository->activateWithCouponCode(Uuid::fromString($integration->id), $couponCode);
            $integration->dispatchActivateWithCoupon();

            return Action::message('Integration ' . $integration->name . ' activated with coupon ' . $fields->get('coupon'));
        } catch (ModelNotFoundException $exception) {
            return Action::danger($couponCode . ' is not an valid coupon.');
        }
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Coupon', 'coupon')
                ->rules(
                    'required',
                    'exists:coupons,code'
                ),
        ];
    }
}
