<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Nova\Actions\UdbOrganizer\ApproveUdbOrganizer;
use App\Nova\Actions\UdbOrganizer\RejectUdbOrganizer;
use App\Nova\Actions\UdbOrganizer\RevokeUdbOrganizer;
use App\Nova\Filters\UdbOrganizerStatusFilter;
use App\Nova\Resource;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\ClientCredentialsContextFactory;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\Dto\UiTPASPermissionDetail;
use App\UiTPAS\UiTPASApi;
use App\UiTPAS\UiTPASApiInterface;
use App\UiTPAS\UiTPASConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin UdbOrganizerModel
 */
final class UdbOrganizer extends Resource
{
    public static string $model = UdbOrganizerModel::class;

    public static $title = 'organizer_id';

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'integration_id',
        'organizer_id',
        'status',
    ];

    public static function label(): string
    {
        return 'UiTPAS organizers';
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new UdbOrganizerStatusFilter(),
        ];
    }

    /** @return array<Field> */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly()
                ->hideFromIndex(),

            Text::make('organizer id', 'organizer_id')
                ->readonly(),

            Text::make('Integration', static function (UdbOrganizerModel $model) {
                $integrationRepository = App::get(IntegrationRepository::class);
                $integration = $integrationRepository->getById($model->toDomain()->integrationId);

                return sprintf(
                    '<a href="%s" class="link-default">%s</a>',
                    config('nova.path') . '/resources/integrations/' . $model->toDomain()->integrationId->toString(),
                    $integration->name
                );
            })->asHtml(),

            Text::make('Name', static function (UdbOrganizerModel $model) {
                /** @var UdbOrganizerNameResolver $udbOrganizerNameResolver */
                $udbOrganizerNameResolver = App::get(UdbOrganizerNameResolver::class);

                /** @var SearchService $searchService */
                $searchService = App::get(SearchService::class);

                $name = $udbOrganizerNameResolver->getName($searchService->findUiTPASOrganizers($model->toDomain()->organizerId));

                if ($name === null) {
                    return 'Niet teruggevonden in UDB3';
                }

                return sprintf(
                    '<a href="%s" target="_blank" class="link-default">%s</a>',
                    config(UiTPASConfig::UDB_BASE_URI->value) . 'organizers/' . $model->toDomain()->organizerId . '/preview',
                    $name
                );
            })->asHtml(),

            Text::make('Status', static function (UdbOrganizerModel $model) {
                $udbOrganizerStatus = $model->toDomain()->status;
                return sprintf(
                    '<span style="color: %s">%s</span>',
                    $udbOrganizerStatus === UdbOrganizerStatus::Approved ? 'green' : 'black',
                    $udbOrganizerStatus->name
                );
            })
                ->asHtml()
                ->readonly(),

            Text::make('UiTPAS', static function (UdbOrganizerModel $model) {
                /** @var IntegrationRepository $integrationRepository */
                $integrationRepository = App::get(IntegrationRepository::class);
                $integration = $integrationRepository->getById($model->toDomain()->integrationId);
                $keycloakClient = $integration->getKeycloakClientByEnv(Environment::Production);

                return sprintf(
                    '<a class="link-default" target="_blank" href="https://test.uitid.be/uitid/rest/admin/uitpas/clientpermissions/%s">Open in UiTPAS</a>',
                    $keycloakClient->clientId
                );
            })->asHtml(),

            Text::make('Permissions', static function (UdbOrganizerModel $model) {
                /** @var UiTPASApi $api */
                $api = App::get(UiTPASApiInterface::class);

                /** @var IntegrationRepository $integrationRepository */
                $integrationRepository = App::get(IntegrationRepository::class);

                $udbOrganizer = $model->toDomain();

                $integration = $integrationRepository->getById($udbOrganizer->integrationId);

                $context = ClientCredentialsContextFactory::getUitIdProdContext();
                $permission = $api->fetchPermissions(
                    $context,
                    $udbOrganizer->organizerId,
                    $integration->getKeycloakClientByEnv($context->environment)->clientId
                );

                if (!$permission instanceof UiTPASPermission) {
                    return '';
                }

                $items = $permission->permissionDetails->map(function (UiTPASPermissionDetail $detail) {
                    return '<li>✅ ' . $detail->label . '</li>';
                })->toArray();

                return '<ul>' . implode('', $items) . '</ul>';
            })
                ->asHtml()
                ->onlyOnDetail(),

            DateTime::make('Requested on', 'created_at')
                ->sortable()
                ->onlyOnIndex(),

            HasMany::make('Activity Log'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        $actions = [];
        if (config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            $approveUdbOrganizer = new ApproveUdbOrganizer(
                App::make(UdbOrganizerRepository::class),
                App::make(IntegrationRepository::class),
                App::make(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdProdContext(),
            );

            $revokeUdbOrganizer = new RevokeUdbOrganizer(
                App::make(UdbOrganizerRepository::class),
                App::make(IntegrationRepository::class),
                App::make(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdProdContext(),
            );

            $actions[] = $approveUdbOrganizer
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to active this organizer in UiTPAS?')
                ->confirmButtonText('Activate')
                ->cancelButtonText('Cancel')
                ->canRun(fn (Request $request, UdbOrganizerModel $model) => $model->toDomain()->status === UdbOrganizerStatus::Pending)
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->isStatusPending());

            $actions[] = App::make(RejectUdbOrganizer::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to reject this organizer request?')
                ->confirmButtonText('Reject')
                ->cancelButtonText('Cancel')
                ->canRun(fn (Request $request, UdbOrganizerModel $model) => $model->toDomain()->status === UdbOrganizerStatus::Pending)
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->isStatusPending());

            $actions[] = $revokeUdbOrganizer
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to revoke these organizer permissions?')
                ->confirmButtonText('Revoke')
                ->cancelButtonText('Cancel')
                ->canRun(fn (Request $request, UdbOrganizerModel $model) => $model->toDomain()->status === UdbOrganizerStatus::Approved)
                ->canSee(fn (Request $request) => $request instanceof ActionRequest || $this->isStatusApproved());
        }

        return $actions;
    }

    private function isStatusPending(): bool
    {
        return $this->status === UdbOrganizerStatus::Pending->value;
    }

    private function isStatusApproved(): bool
    {
        return $this->status === UdbOrganizerStatus::Approved->value;
    }
}
