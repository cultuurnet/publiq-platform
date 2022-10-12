<?php

declare(strict_types=1);

namespace App\Nova;

use App\Domain\Users\Models\UserModel;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

final class User extends Resource
{
    public static string $model = UserModel::class;

    public static $title = 'name';

    /**
     * @var array<string>
     */
    public static $search = [
        'name', 'email',
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

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:user,email')
                ->updateRules('unique:user,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),
        ];
    }
}
