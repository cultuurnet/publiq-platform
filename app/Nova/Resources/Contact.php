<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Models\ContactModel;
use App\Nova\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
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
        'id',
        'first_name',
        'last_name',
        'email',
    ];

    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->select('contacts.*')
            ->leftJoin('integrations', 'integration_id', '=', 'integrations.id');
    }

    /**
     * @return array<Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()
                ->readonly()
                ->hideFromIndex(),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email:filter', 'max:255'),

            Select::make('Type')
                ->filterable(
                    function (NovaRequest $request, Builder $query, $value): void {
                        $query->where('contacts.type', 'LIKE', $value);
                    }
                )
                ->sortable()
                ->options([
                    ContactType::Functional->value => ContactType::Functional->name,
                    ContactType::Technical->value => ContactType::Technical->name,
                    ContactType::Contributor->value => ContactType::Contributor->name,
                ])
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->rules(['required', function ($attribute, $value, $fail) use ($request) {
                    if ($value !== ContactType::Contributor->value) {
                        $integrationId = $request->input('integration');
                        if (ContactModel::where('type', $value)
                            ->where('integration_id', $integrationId)
                            ->count() > 0) {
                            $fail('Only 1 ' . $value . ' contact per integration is allowed.');
                        }
                    }
                }]),

            Text::make('First Name', 'first_name')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            Text::make('Last Name', 'last_name')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            BelongsTo::make('Integration')
                ->sortable()
                ->withMeta(['sortableUriKey' => 'integrations.name'])
                ->withoutTrashed()
                ->readonly(fn (NovaRequest $request) => $request->isUpdateOrUpdateAttachedRequest())
                ->rules('required'),

            InsightlyLink::make('Insightly ID', fn () => $this->insightlyId())
                ->type(InsightlyType::Contact),

            HasMany::make('Activity Log'),
        ];
    }
}
