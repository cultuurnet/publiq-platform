<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Models\AdminInformationModel;
use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

final class AdminInformation extends Resource
{
    public static string $model = AdminInformationModel::class;

    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Integration')
                ->withoutTrashed()
                ->readonly(),

            Boolean::make('On Hold'),

            Text::make('Comment'),
        ];
    }
}
