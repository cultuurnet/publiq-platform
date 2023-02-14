<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Publiq\InsightlyLink\InsightlyLink;
use Publiq\InsightlyLink\InsightlyType;

/**
 * @mixin ContactModel
 */
final class Contact extends Resource
{
    public static string $model = ContactModel::class;

    public static $title = 'email';

    /**
     * @var array<string>
     */
    public static $search = [
        'first_name',
        'last_name',
        'email',
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

            Select::make('Type')
                ->options([
                    ContactType::Functional->value => ContactType::Functional->name,
                    ContactType::Technical->value => ContactType::Technical->name,
                    ContactType::Contributor->value => ContactType::Contributor->name,
                ])
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->rules('required'),

            Text::make('First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            Text::make('Last Name', 'last_name')
                ->sortable()
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:255'),

            BelongsTo::make('Integration')
                ->withoutTrashed()
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->rules('required'),

            InsightlyLink::make('Insightly ID', fn () => $this->insightlyId())
                ->type(InsightlyType::Contact),

            HasMany::make('Activity Log'),
        ];
    }
}
