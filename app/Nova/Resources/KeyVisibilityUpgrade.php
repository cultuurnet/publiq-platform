<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\KeyVisibility;
use App\Domain\KeyVisibilityUpgrades\Models\KeyVisibilityUpgradeModel;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin KeyVisibilityUpgradeModel
 */
final class KeyVisibilityUpgrade extends Resource
{
    public static string $model = KeyVisibilityUpgradeModel::class;

    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->select('key_visibility_upgrades.*')
            ->leftJoin('integrations', 'integration_id', '=', 'integrations.id');
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

            BelongsTo::make('Integration')
                ->sortable()
                ->withMeta(['sortableUriKey' => 'integrations.name'])
                ->withoutTrashed()
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->rules('required'),

            Select::make('Key Visibility')
                ->filterable()
                ->sortable()
                ->options([
                    KeyVisibility::v2->value => KeyVisibility::v2->name,
                ])
                ->rules('required'),

            DateTime::make('Created At')
                ->readonly()
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }
}
