<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Coupons\Models\CouponModel;
use Illuminate\Bus\Queueable;
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

    public function handle(ActionFields $fields, Collection $integrations): void
    {
        $coupon = CouponModel::query()->where('code', '=', $fields->coupon);
        $integration = $integrations->first();

        $coupon->update([
            'integration_id' => $integration->id,
        ]);
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Coupon'),
        ];
    }
}
