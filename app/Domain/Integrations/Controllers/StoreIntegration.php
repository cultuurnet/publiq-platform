<?php

namespace App\Domain\Integrations\Controllers;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegration extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'integrationType' => ['required', 'string'],
            'subscriptionId' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'firstNameOrganisation' => ['required', 'string', 'max:255'],
            'lastNameOrganisation' => ['required', 'string', 'max:255'],
            'emailOrganisation' => ['required', 'string', 'email', 'max:255'],
            'firstNamePartner' => ['required', 'string', 'max:255'],
            'lastNamePartner' => ['required', 'string', 'max:255'],
            'emailPartner' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
