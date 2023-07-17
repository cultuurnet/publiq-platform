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
        return [
            'functional' => [
                'id' => ['required', 'string'],
                'integrationId' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'min:2', 'max:255'],
                'type' => ['required', 'string', 'min:2', 'max:255'],
                'firstName' => ['required', 'string', 'min:2', 'max:255'],
                'lastName' => ['required', 'string', 'min:2', 'max:255'],
                'changed' => ['required', 'boolean'],
            ],
            'technical' => [
                'id' => ['required', 'string'],
                'integrationId' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'min:2', 'max:255'],
                'type' => ['required', 'string', 'min:2', 'max:255'],
                'firstName' => ['required', 'string', 'min:2', 'max:255'],
                'lastName' => ['required', 'string', 'min:2', 'max:255'],
                'changed' => ['required', 'boolean'],
            ],
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
