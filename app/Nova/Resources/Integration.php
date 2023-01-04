<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\UiTiDv1Environment;
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

/**
 * @property string $id
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
                actionLabel: 'Open in UiTiD v1',
                actionUrlCallback: fn (UiTiDv1ConsumerModel $model): ?string => 'https://acc.uitid.be/uitid/rest/admin/serviceconsumers/' . $model->consumer_id,
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
                actionLabel: 'Open in Auth0',
                actionUrlCallback: fn (Auth0ClientModel $model): ?string => 'https://manage.auth0.com/dashboard/eu/publiq-acc/' . $model->client_id . '/settings',
            ),

            HasMany::make('Contacts'),
        ];
    }
}
