<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Coupons\Models\CouponModel;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

final class ActivateIntegration extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(ActionFields $fields, Collection $integrations): array
    {
        try {
            $integration = $integrations->first();
            $coupon = CouponModel::query()
                ->where('code', '=', $fields->get('coupon'))
                ->whereNull('integration_id')
                ->firstOrFail();

            $coupon->update([
                'integration_id' => $integration->id,
                ]);
            return Action::message('Integration ' . $integration->name . ' activated with coupon ' . $fields->get('coupon'));
        } catch (ModelNotFoundException $exception) {
            return Action::danger($fields->get('coupon') . ' is not an valid coupon.');
        }
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Coupon'),
        ];
    }
}
