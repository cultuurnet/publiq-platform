<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Coupons\Models\CouponModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

/**
 * @mixin CouponModel
 */
final class Coupon extends Resource
{
    public static string $model = CouponModel::class;

    public static $title = 'code';

    /**
     * @var array<string>
     */
    public static $search = [
        'code'
    ];

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(),
            BelongsTo::make('Integration')
                ->withoutTrashed(),
            Text::make('Coupon code', 'code'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            (new DownloadExcel())->withHeadings(),
        ];
    }
}
