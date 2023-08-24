<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Nova\Actions\ActivateIntegrationWithOrganization;
use App\Nova\Actions\ActivateIntegrationWithCoupon;
use App\Nova\Actions\BlockIntegration;
use App\Nova\Resource;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
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
        'id',
        'name',
        'description',
    ];

    /**
     * @return array<Field|ResourceTool>
     */
    public function fields(NovaRequest $request): array
    {
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
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('Type')
                ->filterable()
                ->sortable()
                ->options([
                    IntegrationType::EntryApi->value => IntegrationType::EntryApi->name,
                    IntegrationType::SearchApi->value => IntegrationType::SearchApi->name,
                    IntegrationType::Widgets->value => IntegrationType::Widgets->name,
                ])
                ->rules('required'),

            Text::make('Description')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            Select::make('Status')
                ->filterable()
                ->sortable()
                ->exceptOnForms()
                ->options([
                    IntegrationStatus::Draft->value => IntegrationStatus::Draft->name,
                    IntegrationStatus::Active->value => IntegrationStatus::Active->name,
                    IntegrationStatus::Blocked->value => IntegrationStatus::Blocked->name,
                    IntegrationStatus::Deleted->value => IntegrationStatus::Deleted->name,
                    IntegrationStatus::PendingApprovalIntegration->value => IntegrationStatus::PendingApprovalIntegration->name,
                    IntegrationStatus::PendingApprovalPayment->value => IntegrationStatus::PendingApprovalPayment->name,
                ])
                ->default(IntegrationStatus::Draft->value),

            BelongsTo::make('Subscription')
                ->filterable()
                ->sortable()
                ->withoutTrashed()
                ->rules('required'),

            BelongsTo::make('Organization')
                ->withoutTrashed()
                ->exceptOnForms()
                ->hideFromIndex()
                ->nullable(),

            DateTime::make('Created', 'created_at')
                ->readonly()
                ->onlyOnIndex()
                ->filterable()
                ->sortable(),

            InsightlyLink::make('Insightly Opportunity Id', fn () => $this->insightlyOpportunityId())
                ->type(InsightlyType::Opportunity),

            InsightlyLink::make('Insightly Project Id', fn () => $this->insightlyProjectId())
                ->type(InsightlyType::Project),

            Text::make('Coupon', function () {
                if ($this->couponId() === null) {
                    return null;
                }
                $couponUrl = config('nova.path') . '/resources/coupons/' . $this->couponId();
                return '<a href="' . $couponUrl . '" class="link-default">' . $this->couponCode() . '</a>';
            })
                ->asHtml()
                ->onlyOnDetail(),

            HasMany::make('UiTiD v1 Consumer Credentials', 'uiTiDv1Consumers', UiTiDv1::class),
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

            HasMany::make('Urls', 'urls', IntegrationUrl::class),

            HasMany::make('Activity Log'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            (new ActivateIntegrationWithCoupon(App::make(IntegrationRepository::class)))
                ->showOnDetail()
                ->showInline()
                ->confirmText('Are you sure you want to activate this integration with a coupon?')
                ->confirmButtonText('Activate')
                ->cancelButtonText("Don't activate")
                ->canRun(function ($request, $model) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return $model->status === IntegrationStatus::Draft->value;
                }),

            (new ActivateIntegrationWithOrganization(
                App::make(IntegrationRepository::class),
                App::make(OrganizationRepository::class)
            ))
                ->showOnDetail()
                ->showInline()
                ->confirmText('Are you sure you want to activate this integration with an organization?')
                ->confirmButtonText('Activate')
                ->cancelButtonText("Don't activate")
                ->canRun(function ($request, $model) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return $model->status === IntegrationStatus::Draft->value;
                }),

            (new BlockIntegration())
                ->showOnDetail()
                ->showInline()
                ->confirmText('Are you sure you want to block this integration?')
                ->confirmButtonText('Block')
                ->cancelButtonText("Don't block")
                ->canRun(function ($request, $model) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }

                    return $model->status !== IntegrationStatus::Blocked->value;
                }),
        ];
    }
}
