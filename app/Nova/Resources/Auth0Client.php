<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\CachedAuth0ClientGrants;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
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
            Text::make('Visible for integrator', static function (Auth0ClientModel $model) {
                $auth0Client = $model->toDomain();
                /** @var Integration $integration */
                $integration = App::get(IntegrationRepository::class)->getById($auth0Client->integrationId);
                $isVisible = $integration->isKeyVisibleForEnvironment($auth0Client->tenant);
                return sprintf(
                    '<span style="color: %s">%s</span>',
                    $isVisible ? 'default' : 'silver',
                    $isVisible ? 'Yes' : 'No'
                );
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
}
