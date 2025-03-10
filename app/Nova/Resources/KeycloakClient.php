<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\CachedKeycloakClientStatus;
use App\Keycloak\Exception\KeyCloakApiFailed;
use App\Keycloak\Models\KeycloakClientModel;
use App\Keycloak\Realms;
use App\Nova\ActionGuards\ActionGuard;
use App\Nova\ActionGuards\Keycloak\BlockKeycloakClientGuard;
use App\Nova\ActionGuards\Keycloak\UnblockKeycloakClientGuard;
use App\Nova\Actions\Keycloak\BlockKeycloakClient;
use App\Nova\Actions\Keycloak\UnblockKeycloakClient;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

    public static function defaultOrderings(Builder $query): Builder
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
                ->options([
                    Environment::Acceptance->value => Environment::Acceptance->name,
                    Environment::Testing->value => Environment::Testing->name,
                    Environment::Production->value => Environment::Production->name,
                ]),
            Text::make('Status', function (KeycloakClientModel $model) {
                $client = $model->toDomain();

                try {
                    if ($this->getKeycloakClientStatus()->isClientBlocked($client)) {
                        return '<span style="color: red;">Blocked</span>';
                    }

                    return '<span style="color: green;">Active</span>';
                } catch (KeyCloakApiFailed) {
                    return '<span style="color: orange;">Failed to sync</span>';
                }
            })->asHtml(),
            Text::make('Visible for integrator', static function (KeycloakClientModel $model) {
                $keycloakClientModel = $model->toDomain();
                /** @var Integration $integration */
                $integration = App::get(IntegrationRepository::class)->getById($keycloakClientModel->integrationId);
                $isVisible = $integration->isKeyVisibleForEnvironment($keycloakClientModel->environment);
                return sprintf(
                    '<span style="color: %s">%s</span>',
                    $isVisible ? 'default' : 'silver',
                    $isVisible ? 'Yes' : 'No'
                );
            })->asHtml(),
            Text::make('client_id', function (KeycloakClientModel $model) {
                return $model->toDomain()->clientId;
            })
                ->readonly(),
            Text::make('client_secret')
                ->readonly(),
            Text::make('Open', function (KeycloakClientModel $model) {
                $client = $model->toDomain();
                $realm = App::get(Realms::class)->getRealmByEnvironment($client->environment);
                $url = $realm->baseUrl . 'dashboard/' . $realm->internalName . '/clients/' . urlencode($client->clientId) . '/settings';

                return sprintf('<a href="%s" class="link-default" target="_blank">Open in Keycloak</a>', $url);
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
            App::make(UnblockKeycloakClient::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to unblock this client?')
                ->confirmButtonText('Unblock')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canEnable($request, $this->resource))
                ->canRun(fn (Request $request, KeycloakClientModel $model) => $this->canEnable($request, $model)),

            App::make(BlockKeycloakClient::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to block this client?')
                ->confirmButtonText('Block')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canDisable($request, $this->resource))
                ->canRun(fn (Request $request, KeycloakClientModel $model) => $this->canDisable($request, $model)),
        ];
    }

    private function canEnable(Request $request, ?KeycloakClientModel $model): bool
    {
        return $this->can($request, $model, App::make(UnblockKeycloakClientGuard::class));
    }

    private function canDisable(Request $request, ?KeycloakClientModel $model): bool
    {
        return $this->can($request, $model, App::make(BlockKeycloakClientGuard::class));
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
