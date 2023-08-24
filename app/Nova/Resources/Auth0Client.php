<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Contacts\Models\ContactModel;
use App\Nova\Actions\Auth0\BlockClient;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
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


    /**
     * @var array<string>
     */
    public static $search = [
        'integration_id',
        'auth0_client_id',
        'auth0_client_secret',
        'auth0_tenant',
    ];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->select('auth0_clients.*')
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

                    return sprintf('<a href="%s" class="link-default">Open in Auth0</a>', $url);
                }

                return null;
            })->asHtml(),
            BelongsTo::make('Integration')
                ->sortable()
                ->withMeta(['sortableUriKey' => 'integrations.name'])
                ->withoutTrashed()
                ->readonly()
                ->rules('required'),
        ];
    }

    public static function label(): string
    {
        return 'Auth0 Clients';
    }

    public function actions(NovaRequest $request): array
    {
        return [
            (new BlockClient())->showInline(),
        ];
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
