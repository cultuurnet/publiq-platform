<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIntegrationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'integrationName' => ['required_without:description', 'string', 'max:255'],
            'description' => ['required_without:integrationName', 'string', 'max:255'],
            'website' => [
                Rule::requiredIf($this->input('integrationType') === 'uitpas'),
                'nullable',
                'url:http,https',
                'max:255',
            ],
        ];
    }
}
