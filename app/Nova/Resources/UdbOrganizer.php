<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Nova\Resource;
use App\Search\UdbOrganizerNameResolver;
use App\Search\Sapi3\SearchService;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin UdbOrganizerModel
 */
final class UdbOrganizer extends Resource
{
    public static string $model = UdbOrganizerModel::class;

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

            Text::make('organizer id', 'organizer_id')
                ->readonly(),

            Text::make('Name', static function (UdbOrganizerModel $model) {
                /** @var UdbOrganizerNameResolver $udbOrganizerNameResolver */
                $udbOrganizerNameResolver = App::get(UdbOrganizerNameResolver::class);

                /** @var SearchService $searchService */
                $searchService = App::get(SearchService::class);

                return sprintf(
                    '<a href="%s" target="_blank" class="link-default">%s</a>',
                    config(UiTPASConfig::UDB_BASE_URI->value) . 'organizers/' . $model->toDomain()->organizerId . '/preview',
                    $udbOrganizerNameResolver->getName($searchService->findUiTPASOrganizers($model->toDomain()->organizerId)) ?? 'Niet teruggevonden in UDB3'
                );
            })->asHtml(),

            HasMany::make('Activity Log'),
        ];
    }
}
