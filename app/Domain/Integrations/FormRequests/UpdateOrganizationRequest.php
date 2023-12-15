<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrganizationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new CreateOrganizationRequest())->rules(),
            [
                'organization.id' => ['required', 'string'],
            ]
        );
    }
}
