<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Models\UdbOrganizerModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Nova\Actions\ActivateUdbOrganizer;
use App\Nova\Actions\RejectUdbOrganizer;
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

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'integration_id',
        'organizer_id',
        'status',
    ];

    /** @return array<Field> */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly()
                ->hideFromIndex(),

            Text::make('organizer id', 'organizer_id')
                ->readonly(),

            Text::make('Name', static function (UdbOrganizerModel $model) {
                /** @var UdbOrganizerNameResolver $udbOrganizerNameResolver */
                $udbOrganizerNameResolver = App::get(UdbOrganizerNameResolver::class);

                /** @var SearchService $searchService */
                $searchService = App::get(SearchService::class);

                return sprintf(
                    '<a href="%s" target="_blank" class="link-default">%s</a>',
                    config(UiTPASConfig::UDB_BASE_URI->value) . 'organizers/' . $model->toDomain()->organizerId . '/preview',
                    $udbOrganizerNameResolver->getName($searchService->findUiTPASOrganizers($model->toDomain()->organizerId)) ?? 'Niet teruggevonden in UDB3'
                );
            })->asHtml(),

            Text::make('status')
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

            HasMany::make('Activity Log'),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        $actions = [];
        if (config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            $actions[] = App::make(ActivateUdbOrganizer::class)
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
        }

        return $actions;
    }

    private function isStatusPending(): bool
    {
        return $this->status === UdbOrganizerStatus::Pending->value;
    }
}
