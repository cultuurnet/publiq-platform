<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Organizations\Models\AddressModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

final class Address extends Resource
{
    public static string $model = AddressModel::class;

    public static $title = 'street';

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'street',
        'zip',
        'city',
        'country',
    ];

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly(),

            Text::make('Street')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Zip')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('City')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Country')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsTo::make('Organization'),
        ];
    }
}
