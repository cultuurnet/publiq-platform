<?php

declare(strict_types=1);

namespace App\Nova\Resources;

use App\Domain\Contacts\Models\ContactKeyVisibilityModel;
use App\Domain\Integrations\KeyVisibility;
use App\Nova\Resource;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin ContactKeyVisibility
 */
final class ContactKeyVisibility extends Resource
{
    public static string $model = ContactKeyVisibilityModel::class;

    public static $title = 'email';

    /**
     * @var array<string>
     */
    public static $search = [
        'id',
        'email',
    ];

    public static function label(): string
    {
        return 'Contacts Key Visibility';
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
                ->rules('required', 'email', 'max:255'),

            Select::make('Key Visibility')
                ->filterable()
                ->sortable()
                ->options([
                    KeyVisibility::v1->value => KeyVisibility::v1->name,
                    KeyVisibility::v2->value => KeyVisibility::v2->name,
                    KeyVisibility::all->value => KeyVisibility::all->name,
                ])
                ->rules('required'),
        ];
    }
}
