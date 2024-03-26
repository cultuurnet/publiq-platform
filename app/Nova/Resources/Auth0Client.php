<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\CachedAuth0ClientGrants;
use App\Auth0\Models\Auth0ClientModel;
use App\Nova\ActionGuards\ActionGuard;
use App\Nova\ActionGuards\Auth0\ActivateAuth0ClientGuard;
use App\Nova\ActionGuards\Auth0\BlockAuth0ClientGuard;
use App\Nova\Actions\Auth0\ActivateAuth0Client;
use App\Nova\Actions\Auth0\BlockAuth0Client;
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
 * @mixin Auth0ClientModel
 * @property Auth0ClientModel $resource
 */
final class Auth0Client extends Resource
{
    public static string $model = Auth0ClientModel::class;

    public static $title = 'auth0_tenant';

    public static $displayInNavigation = false;

    public static $searchable = false;

    public static function defaultOrderings($query): Builder
    {
        /** @var Builder $query */
        return $query->orderByRaw(
            'CASE
                WHEN auth0_tenant = \'acc\' THEN 1
                WHEN auth0_tenant = \'test\' THEN 2
                WHEN auth0_tenant = \'prod\' THEN 3
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
            Select::make('auth0_tenant')
                ->readonly()
                ->filterable()
                ->options([
                    Auth0Tenant::Acceptance->value => Auth0Tenant::Acceptance->name,
                    Auth0Tenant::Testing->value => Auth0Tenant::Testing->name,
                    Auth0Tenant::Production->value => Auth0Tenant::Production->name,
                ]),
            Text::make('Status', function (Auth0ClientModel $model) {
                $auth0Client = $model->toDomain();
                if (empty(App::get(CachedAuth0ClientGrants::class)->findGrantsOnClient($auth0Client))) {
                    Log::info('Auth0Client - status - ' . $auth0Client->clientId . ': blocked');
                    return '<span style="color: red;">Blocked</span>';
                }

                Log::debug('Auth0Client - status - ' . $auth0Client->clientId . ': active');
                return '<span style="color: green;">Active</span>';
            })->asHtml(),
            Text::make('auth0_client_id')
                ->readonly(),
            Text::make('auth0_client_secret')
                ->readonly(),
            Text::make('Open', function ($model) {
                $auth0ActionUrlTemplates = $this->getActionUrlTemplates();

                if (isset($auth0ActionUrlTemplates[$model->auth0_tenant])) {
                    $url = sprintf($auth0ActionUrlTemplates[$model->auth0_tenant], $model->auth0_client_id);

                    return sprintf('<a href="%s" class="link-default" target="_blank">Open in Auth0</a>', $url);
                }

                return null;
            })->asHtml(),
        ];
    }

    public static function label(): string
    {
        return 'Auth0 Clients';
    }

    private function getActionUrlTemplates(): array
    {
        return array_filter(
            array_map(
                static fn (array $tenantConfig): ?string => $tenantConfig['clientDetailUrlTemplate'] ?? null,
                config('auth0.tenants')
            )
        );
    }

    public function actions(NovaRequest $request): array
    {
        return [
            App::make(ActivateAuth0Client::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to activate this client?')
                ->confirmButtonText('Activate')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canActivate($request, $this->resource))
                ->canRun(fn (Request $request, Auth0ClientModel $model) => $this->canActivate($request, $model)),

            App::make(BlockAuth0Client::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to block this client?')
                ->confirmButtonText('Block')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canBlock($request, $this->resource))
                ->canRun(fn (Request $request, Auth0ClientModel $model) => $this->canBlock($request, $model)),
        ];
    }

    private function canActivate(Request $request, ?Auth0ClientModel $model): bool
    {
        return $this->can($request, $model, App::make(ActivateAuth0ClientGuard::class));
    }

    private function canBlock(Request $request, ?Auth0ClientModel $model): bool
    {
        return $this->can($request, $model, App::make(BlockAuth0ClientGuard::class));
    }

    private function can(Request $request, ?Auth0ClientModel $model, ActionGuard $guard): bool
    {
        if ($request instanceof ActionRequest) {
            return true;
        }

        if ($model === null) {
            return false;
        }

        return $guard->canDo($model->toDomain());
    }
}
