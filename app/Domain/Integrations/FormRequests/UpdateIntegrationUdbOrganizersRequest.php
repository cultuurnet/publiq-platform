<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationUdbOrganizersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'organizers' => ['required', 'array'],
            'organizers.*.name' => ['required', 'string'],
            'organizers.*.id' => ['required', 'string'],
        ];
    }
}
