<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Models\IntegrationModel;
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
                title: 'UiTiD v2 (Auth0) Client Credentials',
                modelClassName: Auth0ClientModel::class,
                idColumn: 'auth0_client_id',
                idLabel: 'Client id',
                secretColumn: 'auth0_client_secret',
                secretLabel: 'Client secret',
                environmentColumn: 'auth0_tenant',
                environmentLabel: 'Environment',
                environmentEnumClass: Auth0Tenant::class,
                filterColumn: 'integration_id',
                filterValue: $this->id,
            ),

            HasMany::make('Contacts'),
        ];
    }
}
