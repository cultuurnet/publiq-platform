<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Coupons\Models\CouponModel;
use App\Nova\Actions\DistributeCoupon;
use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

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
        'id',
        'code',
        'is_distributed',
    ];

    protected static ?array $defaultSort = [
        'is_distributed' => 'asc',
    ];

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->hideFromIndex(),

            Text::make('Coupon code', 'code')
                ->sortable(),

            Boolean::make('Distributed', 'is_distributed')
                ->filterable()
                ->sortable(),

            BelongsTo::make('Integration')
                ->withoutTrashed()
                ->sortable()
                ->nullable(),

            HasMany::make('Activity Log'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            (new DistributeCoupon())->canRun(fn () => true),
        ];
    }
}
