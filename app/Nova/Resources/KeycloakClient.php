<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\RealmCollection;
use App\Nova\ActionGuards\ActionGuard;
use App\Nova\ActionGuards\Keycloak\DisableKeycloakClientGuard;
use App\Nova\ActionGuards\Keycloak\EnableKeycloakClientGuard;
use App\Nova\Actions\Keycloak\DisableKeycloakClient;
use App\Nova\Actions\Keycloak\EnableKeycloakClient;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
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

                if (! $this->getKeycloakClientStatus()->isClientEnabled($client)) {
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
        return [
            App::make(EnableKeycloakClient::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to enable this client?')
                ->confirmButtonText('Enable')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canEnable($request, $this->resource))
                ->canRun(fn (Request $request, KeycloakClientModel $model) => $this->canEnable($request, $model)),

            App::make(DisableKeycloakClient::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to disable this client?')
                ->confirmButtonText('Disable')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canDisable($request, $this->resource))
                ->canRun(fn (Request $request, KeycloakClientModel $model) => $this->canDisable($request, $model)),
        ];
    }

    private function canEnable(Request $request, ?KeycloakClientModel $model): bool
    {
        return $this->can($request, $model, App::make(EnableKeycloakClientGuard::class));
    }

    private function canDisable(Request $request, ?KeycloakClientModel $model): bool
    {
        return $this->can($request, $model, App::make(DisableKeycloakClientGuard::class));
    }

    private function can(Request $request, ?KeycloakClientModel $model, ActionGuard $guard): bool
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        if ($model === null) {
            return false;
        }

        return $guard->canDo($model->toDomain());
    }

    private function getKeycloakClientStatus(): CachedKeycloakClientStatus
    {
        return App::get(CachedKeycloakClientStatus::class);
    }
}
