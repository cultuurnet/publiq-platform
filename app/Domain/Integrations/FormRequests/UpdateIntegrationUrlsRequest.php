<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIntegrationUrlsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $urlValidation = [
            'id' => ['required', 'string'],
            'url' => ['required_without:environment', 'string', 'max:255'],
            'environment' => ['required_without:url', 'string', 'max:255'],
        ];

        return [
            'loginUrl' => $urlValidation,
            'callbackUrls.*' => Rule::forEach(fn () => $urlValidation),
            'logoutUrls.*' => Rule::forEach(fn () => $urlValidation),
        ];
    }
}
