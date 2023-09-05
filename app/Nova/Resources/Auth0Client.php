<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Contacts\Models\ContactModel;
use App\Nova\ActionGuards\Auth0\BlockAuth0ClientGuard;
use App\Nova\Actions\Auth0\BlockAuth0Client;
use App\Nova\Resource;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin ContactModel
 */
final class Auth0Client extends Resource
{
    public static string $model = Auth0ClientModel::class;

    public static $title = 'auth0_tenant';

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'integration_id',
        'auth0_client_id',
        'auth0_client_secret',
        'auth0_tenant',
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
            Select::make('auth0_tenant')
                ->readonly()
                ->filterable()
                ->sortable()
                ->options([
                    Auth0Tenant::Testing->value => Auth0Tenant::Testing->name,
                    Auth0Tenant::Acceptance->value => Auth0Tenant::Acceptance->name,
                    Auth0Tenant::Production->value => Auth0Tenant::Production->name,
                ]),
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

            (new BlockAuth0Client())
                ->showOnDetail()
                ->showInline()
                ->confirmText('Are you sure you want to block this client?')
                ->confirmButtonText('Block')
                ->cancelButtonText("Don't block")
                ->canRun(function ($request, $model) {
                    /** @var BlockAuth0ClientGuard $guard */
                    $guard = App::make(BlockAuth0ClientGuard::class);
                    return $guard->canDo($model->toDomain());
                }),
        ];
    }
}
