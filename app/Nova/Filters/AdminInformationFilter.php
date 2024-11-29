<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

final class AdminInformationFilter extends Filter
{
    public $name = 'On hold';

    public const ON_HOLD_COLUMN = 'on_hold';

    private const ON_HOLD = 'on_hold';
    private const NOT_ON_HOLD = 'not_on_hold';

    public function apply(NovaRequest $request, $query, $value): Builder
    {
        if ($value === self::ON_HOLD) {
            return $query->whereHas('adminInformation', function ($q) {
                $q->where(self::ON_HOLD_COLUMN, '=', 1);
            });
        }

        if ($value === self::NOT_ON_HOLD) {
            return $query->whereDoesntHave('adminInformation', function ($q) {
                $q->where(self::ON_HOLD_COLUMN, '=', 1);
            });
        }

        return $query;
    }

    public function options(NovaRequest $request): array
    {
        return [
            'On hold' => self::ON_HOLD,
            'Not on hold' => self::NOT_ON_HOLD,
        ];
    }
}
