<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Organizations\Models\OrganizationModel;
use App\Nova\Resource;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Publiq\InsightlyLink\InsightlyLink;
use Publiq\InsightlyLink\InsightlyType;

/**
 * @mixin OrganizationModel
 */
final class Organization extends Resource
{
    public static string $model = OrganizationModel::class;

    public static $title = 'name';

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'name',
        'vat',
        'invoice_email',
        'street',
        'zip',
        'city',
        'country',
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

            Text::make('Street')
                ->hideFromIndex()
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('City')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Zip')
                ->filterable()
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Country')
                ->hideFromIndex()
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Invoice Email', 'invoice_email')
                ->sortable()
                ->rules('required_with:vat', 'nullable', 'email:filter', 'max:255'),

            Text::make('Vat')
                ->hideFromIndex()
                ->sortable()
                ->rules('required_with:invoice_email', 'max:255'),

            InsightlyLink::make('Insightly ID', fn () => $this->insightlyId())
                ->type(InsightlyType::Organization),

            HasMany::make('Activity Log'),
        ];
    }
}
