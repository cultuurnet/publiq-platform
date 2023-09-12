<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBillingInfoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
                'organisation.id' => ['required', 'string'],
                'organisation.name' => ['required', 'string', 'max:255'],
                'organisation.address.street' => ['required', 'string', 'max:255'],
                'organisation.address.zip' => ['required', 'string', 'max:255'],
                'organisation.address.city' => ['string', 'max:255'],
                'organisation.vat' => ['string', 'max:255'],
        ];
    }
}
