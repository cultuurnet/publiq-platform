<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

final class AdminInformationFilter extends BooleanFilter
{
    public $name = 'On hold';

    public function apply(NovaRequest $request, $query, $value): Builder
    {
        if (empty($value['on_hold'])) {
            return $query;
        }

        return $query->whereHas('adminInformation', function ($q) {
            $q->where('on_hold', '=', 1);
        });
    }

    public function options(NovaRequest $request): array
    {
        return [
            'On Hold' => 'on_hold',
        ];
    }
}
