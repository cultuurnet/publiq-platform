<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Nova\Actions\ActivateIntegration;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\ResourceTool;
use Publiq\ClientCredentials\ClientCredentials;
use Publiq\InsightlyLink\InsightlyLink;
use Publiq\InsightlyLink\InsightlyType;

/**
 * @mixin IntegrationModel
 */
final class Integration extends Resource
{
    public static string $model = IntegrationModel::class;

    public static $title = 'name';

    /**
     * @var array<string>
     */
    public static $search = [
        'name',
        'description',
    ];

    /**
     * @return array<Field|ResourceTool>
     */
    public function fields(NovaRequest $request): array
    {
        $uitidEnvironmentsConfig = config('uitidv1.environments');
        $uitidEnvironments = array_keys($uitidEnvironmentsConfig);
        $uitidActionUrlTemplates = array_filter(
            array_map(
                static fn (array $envConfig): ?string => $envConfig['consumerDetailUrlTemplate'] ?? null,
                $uitidEnvironmentsConfig
            )
        );

        $auth0TenantsConfig = config('auth0.tenants');
        $auth0Tenants = array_keys($auth0TenantsConfig);
        $auth0ActionUrlTemplates = array_filter(
            array_map(
                static fn (array $tenantConfig): ?string => $tenantConfig['clientDetailUrlTemplate'] ?? null,
                $auth0TenantsConfig
            )
        );

        return [
            ID::make()
                ->readonly(),

            Select::make('Type')
                ->options([
                    IntegrationType::EntryApi->value => IntegrationType::EntryApi->name,
                    IntegrationType::SearchApi->value => IntegrationType::SearchApi->name,
                    IntegrationType::Widgets->value => IntegrationType::Widgets->name,
                ])
                ->rules('required'),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Description')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsTo::make('Subscription')
                ->withoutTrashed()
                ->rules('required'),

            Select::make('Status')
                ->options([
                    IntegrationStatus::Draft->value => IntegrationStatus::Draft->name,
                    IntegrationStatus::Active->value => IntegrationStatus::Active->name,
                    IntegrationStatus::Blocked->value => IntegrationStatus::Blocked->name,
                    IntegrationStatus::Deleted->value => IntegrationStatus::Deleted->name,
                ])
                ->default(IntegrationStatus::Draft->value),

            InsightlyLink::make('Insightly ID', fn () => $this->insightlyId())
                ->type(InsightlyType::Opportunity),

            Text::make('Coupon', fn () => $this->couponCode())->onlyOnDetail(),

            ClientCredentials::make(
                title: 'UiTiD v1 Consumer Credentials',
                modelClassName: UiTiDv1ConsumerModel::class,
                columns: [
                    'environment' => 'Environment',
                    'api_key' => 'API key',
                    'consumer_key' => 'Consumer key',
                    'consumer_secret' => 'Consumer secret',
                ],
                filterColumn: 'integration_id',
                filterValue: $this->id,
                sortColumn: 'environment',
                sortValues: $uitidEnvironments,
                actionLabel: 'Open in UiTiD v1',
                actionUrlCallback: static function (UiTiDv1ConsumerModel $model) use ($uitidActionUrlTemplates): ?string {
                    if (isset($uitidActionUrlTemplates[$model->environment])) {
                        return sprintf($uitidActionUrlTemplates[$model->environment], $model->consumer_id);
                    }
                    return null;
                },
            ),

            ClientCredentials::make(
                title: 'UiTiD v2 Client Credentials (Auth0)',
                modelClassName: Auth0ClientModel::class,
                columns: [
                    'auth0_tenant' => 'Environment',
                    'auth0_client_id' => 'Client id',
                    'auth0_client_secret' => 'Client secret',
                ],
                filterColumn: 'integration_id',
                filterValue: $this->id,
                sortColumn: 'auth0_tenant',
                sortValues: $auth0Tenants,
                actionLabel: 'Open in Auth0',
                actionUrlCallback: static function (Auth0ClientModel $model) use ($auth0ActionUrlTemplates): ?string {
                    if (isset($auth0ActionUrlTemplates[$model->auth0_tenant])) {
                        return sprintf($auth0ActionUrlTemplates[$model->auth0_tenant], $model->auth0_client_id);
                    }
                    return null;
                },
            ),

            HasMany::make('Contacts'),

            HasMany::make('ActivityLog'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            (new ActivateIntegration())->onlyOnTableRow(
                $this->model()->status === IntegrationStatus::Draft->value
            ),
        ];
    }
}
