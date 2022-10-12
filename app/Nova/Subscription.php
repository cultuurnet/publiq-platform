<?php

declare(strict_types=1);

namespace App\Nova;

use App\Domain\Subscriptions\BillingInterval;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Models\SubscriptionModel;
use Laravel\Nova\Fields\Currency as CurrencyField;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

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
            Text::make('Id')
                ->readonly(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Description')
                ->rules('required', 'max:255')
                ->onlyOnForms(),

            CurrencyField::make('Price')
                ->currency(Currency::EUR->value)
                ->min(0)
                ->step(0.01)
                ->rules('required'),

            Select::make('Interval', 'billing_interval')
                ->options([
                    BillingInterval::Monthly->value => BillingInterval::Monthly->name,
                    BillingInterval::Yearly->value => BillingInterval::Yearly->name,
                ])
                ->rules('required'),

            CurrencyField::make('Fee')
                ->currency(Currency::EUR->value)
                ->min(0)
                ->step(0.01),

            Text::make('Currency')
                ->fillUsing(
                    fn ($request, $model, $attribute) => $model->{$attribute} = Currency::EUR->value
                )
                ->hide(),
        ];
    }
}
