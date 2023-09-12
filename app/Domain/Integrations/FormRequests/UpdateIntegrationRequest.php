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
            'integrationName' => ['string', 'max:255'],
            'description' => ['string', 'max:255'],
            'loginUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string'],
                ];
            }),
            'newIntegrationUrl' => [
                'url' => ['required', 'string'],
                'environment' => ['required', 'string'],
            ],
        ];
    }

}
