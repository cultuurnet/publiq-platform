<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Nova\ActionGuards\ActionGuard;
use App\Nova\ActionGuards\UiTiDv1\ActivateUiTiDv1ConsumerGuard;
use App\Nova\ActionGuards\UiTiDv1\BlockUiTiDv1ConsumerGuard;
use App\Nova\Actions\UiTiDv1\ActivateUiTiDv1Consumer;
use App\Nova\Actions\UiTiDv1\BlockUiTiDv1Consumer;
use App\Nova\Actions\UiTiDv1\DistributeUiTiDv1Consumer;
use App\Nova\Resource;
use App\UiTiDv1\CachedUiTiDv1Status;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1ConsumerStatus;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin UiTiDv1ConsumerModel
 * @property UiTiDv1ConsumerModel $resource
 */
final class UiTiDv1 extends Resource
{
    public static string $model = UiTiDv1ConsumerModel::class;

    public static $title = 'consumer_id';

    public static $displayInNavigation = false;

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'integration_id',
        'consumer_id',
        'consumer_key',
        'consumer_secret',
        'api_key',
        'environment',
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
            Select::make('environment')
                ->readonly()
                ->filterable()
                ->sortable()
                ->options([
                    UiTiDv1Environment::Testing->value => UiTiDv1Environment::Testing->name,
                    UiTiDv1Environment::Acceptance->value => UiTiDv1Environment::Acceptance->name,
                    UiTiDv1Environment::Production->value => UiTiDv1Environment::Production->name,
                ]),
            Text::make('Status', static function (UiTiDv1ConsumerModel $model) {
                $status = App::get(CachedUiTiDv1Status::class)->findStatusOnConsumer($model->toDomain());

                return sprintf(
                    '<span style="color: %s">%s</span>',
                    $status === UiTiDv1ConsumerStatus::Active ? 'green' : 'red',
                    $status->name
                );
            })->asHtml(),
            DateTime::make('distributed_at')
                ->readonly(),
            Text::make('consumer_key')
                ->readonly(),
            Text::make('consumer_secret')
                ->readonly(),
            Text::make('api_key')
                ->readonly(),
            Text::make('Open', function ($model) {
                $uitidActionUrlTemplates = $this->getActionUrlTemplates();

                if (isset($uitidActionUrlTemplates[$model->environment])) {
                    $url = sprintf($uitidActionUrlTemplates[$model->environment], $model->consumer_id);

                    return sprintf('<a href="%s" class="link-default" target="_blank">Open in UiTiD v1</a>', $url);
                }

                return null;
            })->asHtml(),
        ];
    }

    public static function label(): string
    {
        return 'UiTiD v1 consumer';
    }

    private function getActionUrlTemplates(): array
    {
        return array_filter(
            array_map(
                static fn (array $envConfig): ?string => $envConfig['consumerDetailUrlTemplate'] ?? null,
                config('uitidv1.environments')
            )
        );
    }

    public function actions(NovaRequest $request): array
    {
        return [
            App::make(ActivateUiTiDv1Consumer::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to activate this consumer?')
                ->confirmButtonText('Activate')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canActivate($request, $this->resource))
                ->canRun(fn (Request $request, UiTiDv1ConsumerModel $model) => $this->canActivate($request, $model)),

            App::make(BlockUiTiDv1Consumer::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to block this consumer?')
                ->confirmButtonText('Block')
                ->cancelButtonText('Cancel')
                ->canSee(fn (Request $request) => $this->canBlock($request, $this->resource))
                ->canRun(fn (Request $request, UiTiDv1ConsumerModel $model) => $this->canBlock($request, $model)),

            App::make(DistributeUiTiDv1Consumer::class)
                ->exceptOnIndex()
                ->confirmText('Are you sure you want to distribute this consumer?')
                ->confirmButtonText('Distribute')
                ->cancelButtonText("Don't distribute")
                ->canSee(fn (Request $request) => !$this->resource->isDistributed())
                ->canRun(fn (Request $request, UiTiDv1ConsumerModel $model) => !$this->resource->isDistributed()),
        ];
    }

    private function canActivate(Request $request, ?UiTiDv1ConsumerModel $model): bool
    {
        return $this->can($request, $model, App::make(ActivateUiTiDv1ConsumerGuard::class));
    }

    private function canBlock(Request $request, ?UiTiDv1ConsumerModel $model): bool
    {
        return $this->can($request, $model, App::make(BlockUiTiDv1ConsumerGuard::class));
    }

    private function can(Request $request, ?UiTiDv1ConsumerModel $model, ActionGuard $guard): bool
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
