<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Spatie\Activitylog\Models\Activity;

/**
 * @mixin Activity
 */
final class ActivityLog extends Resource
{
    public static string $model = Activity::class;

    public static $title = 'description';

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'description',
        'subject_id',
    ];

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->onlyOnDetail(),

            DateTime::make('Timestamp', 'created_at'),

            Text::make('Action', 'event'),

            Text::make('Item Id', 'subject_id')->onlyOnDetail(),

            Text::make('User Id', 'causer_id'),

            Code::make('Metadata', 'properties')->json(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
}
