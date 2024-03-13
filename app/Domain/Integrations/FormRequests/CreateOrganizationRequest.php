<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateOrganizationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization.name' => ['required', 'string', 'max:255'],
            'organization.invoiceEmail' => ['required', 'string', 'email', 'min:2', 'max:255'],
            'organization.vat' => ['nullable', 'string', 'max:255'],
            'organization.address.street' => ['required', 'string', 'max:255'],
            'organization.address.zip' => ['required', 'string', 'max:255'],
            'organization.address.city' => ['required', 'string', 'max:255'],
            'organization.address.country' => ['required', 'string', 'max:255'],
        ];
    }
}
