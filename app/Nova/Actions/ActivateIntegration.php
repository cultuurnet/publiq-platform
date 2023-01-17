<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Integrations\Models\IntegrationModel;
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

    public function handle(ActionFields $fields, Collection $models): void
    {
        foreach ($models as $model) {
            /** @var IntegrationModel $model */
            $model->activateWithCoupon($fields->coupon);
        }
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Coupon'),
        ];
    }
}
