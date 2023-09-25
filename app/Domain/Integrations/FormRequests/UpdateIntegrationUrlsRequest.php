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
        return [
            'loginUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string', 'max:255'],
                    'environment' => ['required', 'string', 'max:255'],
                ];
            }),
            'callbackUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string', 'max:255'],
                    'environment' => ['required', 'string', 'max:255'],
                ];
            }),
            'logoutUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string', 'max:255'],
                    'environment' => ['required', 'string', 'max:255'],
                ];
            }),
        ];
    }
}
