<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use App\Nova\Resource;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

final class IntegrationUrl extends Resource
{
    public static string $model = IntegrationUrlModel::class;

    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Integration')
                ->withoutTrashed()
                ->sortable(),

            Select::make('Environment')
                ->filterable()
                ->sortable()
                ->options([
                    Environment::Acceptance->value => Environment::Acceptance->name,
                    Environment::Testing->value => Environment::Testing->name,
                    Environment::Production->value => Environment::Production->name,
                ])
                ->rules('required'),

            Select::make('Type')
                ->filterable()
                ->sortable()
                ->options([
                    IntegrationUrlType::Login->value => IntegrationUrlType::Login->name,
                    IntegrationUrlType::Callback->value => IntegrationUrlType::Callback->name,
                    IntegrationUrlType::Logout->value => IntegrationUrlType::Logout->name,
                ])
                ->rules('required'),

            Text::make('Url')
                ->sortable()
                ->rules('required', 'url'),
        ];
    }
}
