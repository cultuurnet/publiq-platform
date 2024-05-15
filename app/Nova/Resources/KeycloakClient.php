<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\RealmCollection;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin KeycloakClientModel
 * @property KeycloakClientModel $resource
 */
final class KeycloakClient extends Resource
{
    public static string $model = KeycloakClientModel::class;

    public static $title = 'realm';

    public static $displayInNavigation = false;

    public static $searchable = false;

    public static function defaultOrderings($query): Builder
    {
        //@todo Change to real keycloak environments
        /** @var Builder $query */
        return $query->orderByRaw(
            'CASE
                WHEN realm = \'acc\' THEN 1
                WHEN realm = \'test\' THEN 2
                WHEN realm = \'prod\' THEN 3
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
            Select::make('realm')
                ->readonly()
                ->filterable()
                ->options(RealmCollection::asArray()),
            Text::make('Status', function (KeycloakClientModel $model) {
                $client = $model->toDomain();
                if (random_int(0, 1)) { //@todo implement status check
                    Log::info('KeycloakClient - status - ' . $client->integrationId . ': blocked');
                    return '<span style="color: red;">Blocked</span>';
                }

                Log::debug('KeycloakClient - status - ' . $client->integrationId . ': active');
                return '<span style="color: green;">Active</span>';
            })->asHtml(),
            Text::make('client_id', function (KeycloakClientModel $model) {
                return $model->toDomain()->integrationId->toString();
            })
                ->readonly(),
            Text::make('client_secret')
                ->readonly(),
            Text::make('Open', function (KeycloakClientModel $model) {
                // I wish I could use my config object, but don't know how to get access to it from here
                $baseUrl = config('keycloak.base_url');

                return sprintf('<a href="%s" class="link-default" target="_blank">Open in Keycloak</a>', $model->toDomain()->getKeycloakUrl($baseUrl));
            })->asHtml(),
        ];
    }

    public static function label(): string
    {
        return 'Keycloak Clients';
    }

    public function actions(NovaRequest $request): array
    {
        //@todo Add block/enable actions
        return [];
    }
}
