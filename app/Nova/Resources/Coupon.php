<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Coupons\Models\CouponModel;
use App\Nova\Actions\DistributeCoupon;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

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
        'code',
        'is_distributed',
    ];

    public static array $defaultSort = [
        'is_distributed' => 'asc'
    ];

    public static function indexQuery(NovaRequest $request, $query):Builder
    {
        if (Coupon::$defaultSort && empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];
            foreach (Coupon::$defaultSort as $field => $order) {
                $query->orderBy($field, $order);
            }
        }

        return $query;
    }

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
                ->sortable(),

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
