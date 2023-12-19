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
        return array_merge(
            (new CreateBillingInfoRequest())->rules(),
            [
                'organization.id' => ['required', 'string'],
            ]
        );
    }
}
