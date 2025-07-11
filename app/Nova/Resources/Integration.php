<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Keycloak\KeycloakConfig;
use App\Nova\Actions\ActivateIntegration;
use App\Nova\Actions\ActivateUitpasIntegration;
use App\Nova\Actions\ApproveIntegration;
use App\Nova\Actions\BlockIntegration;
use App\Nova\Actions\Keycloak\CreateMissingKeycloakClients;
use App\Nova\Actions\OpenWidgetManager;
use App\Nova\Actions\UdbOrganizer\RequestUdbOrganizer;
use App\Nova\Actions\UiTiDv1\CreateMissingUiTiDv1Consumers;
use App\Nova\Actions\UnblockIntegration;
use App\Nova\Filters\AdminInformationFilter;
use App\Nova\Resource;
use App\Search\Sapi3\SearchService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Query\Search\SearchableRelation;
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

    protected static ?array $defaultSort = [
        'created_at' => 'desc',
    ];

    public static $with = [
        'uiTiDv1Consumers',
        'keycloakClients',
        'subscription',
    ];

    public static function searchableColumns(): array
    {
        $output = [
            'id',
            'name',
            'description',
            new SearchableRelation('uiTiDv1Consumers', 'consumer_key'),
        ];

        if (config(KeycloakConfig::KEYCLOAK_CREATION_ENABLED)) {
            $output[] = new SearchableRelation('keycloakClients', 'client_id');
        }

        return $output;
    }

    public function filters(Request $request): array
    {
        return [
            new AdminInformationFilter(),
        ];
    }

    /**
     * @return array<Field|ResourceTool>
     */
    public function fields(NovaRequest $request): array
    {
        $integrationTypes = [
            IntegrationType::EntryApi->value => IntegrationType::EntryApi->name,
            IntegrationType::SearchApi->value => IntegrationType::SearchApi->name,
            IntegrationType::Widgets->value => IntegrationType::Widgets->name,
            IntegrationType::UiTPAS->value => IntegrationType::UiTPAS->name,
        ];

        $fields = [
            ID::make()
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('Type')
                ->filterable()
                ->sortable()
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->options($integrationTypes)
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

            Select::make('Key Visibility')
                ->filterable()
                ->sortable()
                ->required()
                ->dependsOn(
                    ['type'],
                    function (Select $field, NovaRequest $request, FormData $formData) {
                        if ($formData->string('type')->toString() !== IntegrationType::UiTPAS->value) {
                            $field->options([
                                KeyVisibility::v1->value => KeyVisibility::v1->name,
                                KeyVisibility::v2->value => KeyVisibility::v2->name,
                                KeyVisibility::all->value => KeyVisibility::all->name,
                            ]);
                            return;
                        }

                        $field->options([
                            KeyVisibility::v2->value => KeyVisibility::v2->name,
                        ]);
                    }
                ),

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
                ->rules('required')
                ->dependsOn('type', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    $type = $formData->string('type')->toString();

                    if (!$type) {
                        $field->readonly();
                    }

                    $field->relatableQueryUsing(
                        fn (NovaRequest $request, Builder $query) => $query->where('integration_type', $type)
                    );
                }),

            URL::make('Website')
                ->displayUsing(fn () => $this->website)
                ->showOnIndex(false)
                ->dependsOn(
                    ['type'],
                    function (Text $field, NovaRequest $request, FormData $formData) {
                        $rules = ['url:http,https', 'max:255'];
                        $required = true;

                        if ($formData->string('type')->toString() !== IntegrationType::UiTPAS->value) {
                            $rules[] = 'nullable';
                            $required = false;
                        }

                        $field
                            ->required($required)
                            ->rules($rules);
                    }
                ),

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
        ];

        if (config(KeycloakConfig::KEYCLOAK_CREATION_ENABLED)) {
            $fields[] = HasMany::make('Keycloak client Credentials', 'keycloakClients', KeycloakClient::class);
        }

        $fields[] = HasOne::make('Admin Information', 'adminInformation', AdminInformation::class)
            ->onlyOnDetail();

        return array_merge($fields, [
            HasMany::make('UDB3 Organizers', 'udbOrganizers', UdbOrganizer::class)
                ->canSee(function () {
                    /** @var ?IntegrationModel $model */
                    $model = $this->model();
                    return $model && $model->isUiTPAS();
                }),
            HasMany::make('Contacts'),
            HasMany::make('Urls', 'urls', IntegrationUrl::class),
            HasMany::make('Activity Log'),
        ]);
    }

    public function actions(NovaRequest $request): array
    {
        $actions = [
            (new ActivateIntegration(App::make(IntegrationRepository::class)))
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to activate this integration?')
                ->confirmButtonText('Activate')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->canBeActivated() && !$this->isUiTPAS())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->canBeActivated()),

            (new ActivateUitpasIntegration(App::make(IntegrationRepository::class)))
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to activate this integration?')
                ->confirmButtonText('Activate')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->canBeActivated() && $this->isUiTPAS())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->canBeActivated()),

            (new ApproveIntegration(App::make(IntegrationRepository::class)))
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to approve this integration?')
                ->confirmButtonText('Approve')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->canBeApproved())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->canBeApproved()),

            (new OpenWidgetManager())
                ->exceptOnIndex()
                ->withoutConfirmation()
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->isWidgets())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->isWidgets()),

            (new BlockIntegration())
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to block this integration?')
                ->confirmButtonText('Block')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->canBeBlocked())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->canBeBlocked()),

            (new UnblockIntegration())
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to unblock this integration?')
                ->confirmButtonText('Unblock')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->canBeUnblocked())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->canBeUnblocked()),

            (new RequestUdbOrganizer(App::make(UdbOrganizerRepository::class), App::make(SearchService::class)))
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to add an organizer?')
                ->confirmButtonText('Add')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->isUiTPAS())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->isUiTPAS()),

            (new CreateMissingUiTiDv1Consumers())
                ->withName('Create missing UiTiD v1 Consumers')
                ->onlyOnDetail()
                ->confirmText('Are you sure you want to create missing UiTiD v1 consumers for this integration?')
                ->confirmButtonText('Create')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->hasMissingUiTiDv1Consumers())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->hasMissingUiTiDv1Consumers()),
        ];

        if (config(KeycloakConfig::KEYCLOAK_CREATION_ENABLED)) {
            $actions[] = (new CreateMissingKeycloakClients())
                ->withName('Create missing Keycloak clients')
                ->onlyOnDetail()
                ->confirmText('Are you sure you want to create missing Keycloak clients for this integration?')
                ->confirmButtonText('Create')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->hasMissingKeycloakConsumers())
                ->canRun(fn (Request $request, IntegrationModel $model) => $model->hasMissingKeycloakConsumers());
        }

        return $actions;
    }
}
