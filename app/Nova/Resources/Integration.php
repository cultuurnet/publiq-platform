<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Auth0\Auth0Tenant;
use App\Auth0\Models\Auth0ClientModel;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
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
use Publiq\ClientCredentials\ClientCredentials;
use Ramsey\Uuid\Uuid;

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
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        $auth0ClientCredentials = ClientCredentials::make(
            title: 'UiTiD v2 (Auth0) Client Credentials',
            idLabel: 'Client id',
            secretLabel: 'Client secret',
            environmentLabel: 'Environment'
        );

        $auth0Clients = $this->id ? (new EloquentAuth0ClientRepository())->getByIntegrationId(Uuid::fromString($this->id)) : [];
        foreach ($auth0Clients as $auth0Client) {
            $auth0ClientCredentials->withSet(
                $auth0Client->tenant->name,
                $auth0Client->clientId,
                $auth0Client->clientSecret
            );
        }

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

            $auth0ClientCredentials,

            HasMany::make('Contacts'),
        ];
    }
}
