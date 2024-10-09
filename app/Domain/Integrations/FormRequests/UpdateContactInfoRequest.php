<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateContactInfoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
                'functional.id' => ['string'],
                'functional.integrationId' => ['string'],
                'functional.email' => ['string', 'email:filter', 'min:2', 'max:255'],
                'functional.type' => ['string', 'min:2', 'max:255'],
                'functional.firstName' => ['string', 'min:2', 'max:255'],
                'functional.lastName' => ['string', 'min:2', 'max:255'],
                'technical.id' => ['string'],
                'technical.integrationId' => ['string'],
                'technical.email' => ['string', 'email:filter', 'min:2', 'max:255'],
                'technical.type' => ['string', 'min:2', 'max:255'],
                'technical.firstName' => ['string', 'min:2', 'max:255'],
                'technical.lastName' => ['string', 'min:2', 'max:255'],
                'contributors.*' => Rule::forEach(function () {
                    return [
                        'id' => ['required', 'string'],
                        'integrationId' => ['required', 'string'],
                        'email' => ['required', 'string', 'email:filter', 'min:2', 'max:255'],
                        'type' => ['required', 'string', 'min:2', 'max:255'],
                        'firstName' => ['required', 'string', 'min:2', 'max:255'],
                        'lastName' => ['required', 'string', 'min:2', 'max:255'],
                    ];
                }),
        ];
    }
}
