<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Models\UiTdatabankOrganizerModel;
use App\Nova\Resource;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin UiTdatabankOrganizerModel
 */
final class Organizer extends Resource
{
    public static string $model = UiTdatabankOrganizerModel::class;

    public static $title = 'organizer_id';

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'integration_id',
        'organizer_id',
    ];

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly()
                ->hideFromIndex(),

            Text::make('organizer_id')
                ->readonly(),

            HasMany::make('Activity Log'),
        ];
    }
}
