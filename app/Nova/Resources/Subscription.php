<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use Laravel\Nova\Fields\Currency as CurrencyField;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

final class Subscription extends Resource
{
    public static string $model = SubscriptionModel::class;

    public static $title = 'name';

    /**
     * @var array<string>
     */
    public static $search = [
        'name',
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

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Description')
                ->rules('required', 'max:255')
                ->onlyOnForms(),

            Select::make('Integration Type', 'integration_type')
                ->options([
                    IntegrationType::EntryApi->value => IntegrationType::EntryApi->name,
                    IntegrationType::SearchApi->value => IntegrationType::SearchApi->name,
                    IntegrationType::Widgets->value => IntegrationType::Widgets->name,
                ])
                ->rules('required'),

            CurrencyField::make('Subscription price (billed annually)', 'price')
                ->currency(Currency::EUR->value)
                ->min(0)
                ->step(0.01)
                ->rules('required'),

            CurrencyField::make('Setup fee (billed once)', 'fee')
                ->currency(Currency::EUR->value)
                ->min(0)
                ->step(0.01),

            Text::make('Currency')
                ->fillUsing(
                    fn ($request, $model, $attribute) => $model->{$attribute} = Currency::EUR->value
                )
                ->hide()
                ->hideFromIndex(),

            HasMany::make('Activity Log'),
        ];
    }
}
