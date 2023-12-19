<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBillingInfoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organisation.name' => ['required', 'string', 'max:255'],
            'organisation.invoiceEmail' => ['required', 'string', 'email', 'min:2', 'max:255'],
            'organisation.vat' => ['required', 'string', 'max:255'],
            'organisation.address.street' => ['required', 'string', 'max:255'],
            'organisation.address.zip' => ['required', 'string', 'max:255'],
            'organisation.address.city' => ['required', 'string', 'max:255'],
            'organisation.address.country' => ['required', 'string', 'max:255'],
        ];
    }
}
