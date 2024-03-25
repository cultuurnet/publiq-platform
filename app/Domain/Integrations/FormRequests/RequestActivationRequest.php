<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class RequestActivationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new CreateOrganizationRequest())->rules(),
            [
                'coupon' => ['nullable', 'string', 'max:255'],
            ]
        );
    }
}
