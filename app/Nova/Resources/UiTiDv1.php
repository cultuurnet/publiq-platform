<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Nova\Resource;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin UiTiDv1ConsumerModel
 * @property UiTiDv1ConsumerModel $resource
 */
final class UiTiDv1 extends Resource
{
    public static string $model = UiTiDv1ConsumerModel::class;

    public static $title = 'consumer_id';

    public static $displayInNavigation = false;

    public static $searchable = false;

    public static function defaultOrderings(Builder $query): Builder
    {
        /** @var Builder $query */
        return $query->orderByRaw(
            'CASE
                WHEN environment = \'acc\' THEN 1
                WHEN environment = \'test\' THEN 2
                WHEN environment = \'prod\' THEN 3
            END'
        );
    }

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly()
                ->hideFromIndex(),
            Select::make('environment')
                ->readonly()
                ->filterable()
                ->options([
                    UiTiDv1Environment::Acceptance->value => UiTiDv1Environment::Acceptance->name,
                    UiTiDv1Environment::Testing->value => UiTiDv1Environment::Testing->name,
                    UiTiDv1Environment::Production->value => UiTiDv1Environment::Production->name,
                ]),
            Text::make('api_key')
                ->readonly(),
        ];
    }

    public static function label(): string
    {
        return 'UiTiD v1 consumer';
    }

}
