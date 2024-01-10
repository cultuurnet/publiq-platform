<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Nova\Actions\ActivateIntegrationWithCoupon;
use App\Nova\Actions\ActivateIntegrationWithOrganization;
use App\Nova\Actions\BlockIntegration;
use App\Nova\Actions\OpenWidgetManager;
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

            Select::make('Partner status')
                ->filterable()
                ->sortable()
                ->options([
                    IntegrationPartnerStatus::FIRST_PARTY->value => IntegrationPartnerStatus::FIRST_PARTY->value,
                    IntegrationPartnerStatus::THIRD_PARTY->value => IntegrationPartnerStatus::THIRD_PARTY->value,
                ])
                ->default(IntegrationPartnerStatus::THIRD_PARTY->value)
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
            HasMany::make('UiTiD v2 Client Credentials (Auth0)', 'auth0Clients', Auth0Client::class),

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

            (new OpenWidgetManager())
                ->showOnDetail()
                ->showInline()
                ->withoutConfirmation()
                ->canRun(function ($request, $model) {
                    if ($request instanceof ActionRequest) {
                        return true;
                    }
                    return $model->type === IntegrationType::Widgets->value;
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
