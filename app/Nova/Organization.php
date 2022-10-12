<?php

declare(strict_types=1);

namespace App\Nova;

use App\Domain\Organizations\Models\AddressModel;
use App\Domain\Organizations\Models\OrganizationModel;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @property AddressModel $address */
final class Organization extends Resource
{
    public static string $model = OrganizationModel::class;

    public static $title = 'name';

    /**
     * @var array<string>
     */
    public static $search = [
        'name',
        'vat',
        'address.street',
        'address.zip',
        'address.city',
        'address.country',
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

            Text::make('Vat')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(
                'Address',
                fn () => $this->address->street . ', ' . $this->address->zip . ' ' . $this->address->city
            )
                ->onlyOnIndex(),

            HasOne::make('Address'),
        ];
    }
}
