<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIntegrationUrlRequest extends FormRequest
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
                    'url' => ['required', 'string'],
                ];
            }),
            'callbackUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string'],
                ];
            }),
            'logoutUrls.*' => Rule::forEach(function () {
                return [
                    'id' => ['required', 'string'],
                    'url' => ['required', 'string'],
                ];
            }),
        ];
    }
}
