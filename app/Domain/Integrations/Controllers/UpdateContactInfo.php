<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateContactInfo extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // TODO: Check how to fix empty string values failing
        return [
                'functional.id' => ['required', 'string'],
                'functional.integrationId' => ['required', 'string'],
                'functional.email' => ['required', 'string', 'email', 'min:2', 'max:255'],
                'functional.type' => ['required', 'string', 'min:2', 'max:255'],
                'functional.firstName' => ['required', 'string', 'min:2', 'max:255'],
                'functional.lastName' => ['required', 'string', 'min:2', 'max:255'],
                'functional.changed' => ['required', 'boolean'],
                'technical.id' => ['required', 'string'],
                'technical.integrationId' => ['required', 'string'],
                'technical.email' => ['required', 'string', 'email', 'min:2', 'max:255'],
                'technical.type' => ['required', 'string', 'min:2', 'max:255'],
                'technical.firstName' => ['required', 'string', 'min:2', 'max:255'],
                'technical.lastName' => ['required', 'string', 'min:2', 'max:255'],
                'technical.changed' => ['required', 'boolean'],
                'contributors' => ['required', 'array'],
                'contributors.*' => Rule::forEach(function () {
                    return [
                        'id' => ['required', 'string'],
                        'integrationId' => ['required', 'string'],
                        'email' => ['required', 'string', 'email', 'min:2', 'max:255'],
                        'type' => ['required', 'string', 'min:2', 'max:255'],
                        'firstName' => ['required', 'string', 'min:2', 'max:255'],
                        'lastName' => ['required', 'string', 'min:2', 'max:255'],
                        'changed' => ['required', 'boolean'],
                    ];
                }),
    ];
    }
}
