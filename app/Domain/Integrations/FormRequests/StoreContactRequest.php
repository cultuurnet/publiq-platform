<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreContactRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['string', 'min:2', 'max:255'],
            'lastName' => [ 'string', 'min:2', 'max:255'],
            'email' => ['string', 'email', 'min:2', 'max:255'],
        ];
    }
}
