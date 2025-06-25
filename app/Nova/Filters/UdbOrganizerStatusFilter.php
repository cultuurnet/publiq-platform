<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use App\Domain\Integrations\UdbOrganizerStatus;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

final class UdbOrganizerStatusFilter extends Filter
{
    public $name = 'Status';

    public function apply(NovaRequest $request, Builder $query, mixed $value): Builder
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request): array
    {
        return collect(UdbOrganizerStatus::cases())
            ->mapWithKeys(fn ($status) => [$status->name => $status->value])
            ->toArray();
    }
}
