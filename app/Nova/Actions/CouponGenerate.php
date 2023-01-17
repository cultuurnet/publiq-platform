<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Domain\Coupons\Models\CouponModel;
use Faker\Provider\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

final class CouponGenerate extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(ActionFields $fields, Collection $models): array
    {
        return [
            (new CouponModel(['code' => Text::numerify('###########')]))->save(),
        ];
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
