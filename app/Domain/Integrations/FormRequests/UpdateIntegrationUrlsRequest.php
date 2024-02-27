<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateIntegrationUrlsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $urlValidation = [
            'id' => ['required', 'string'],
            'url' => ['required', 'url:https', 'max:255'],
        ];

        return [
            'loginUrls.*' => Rule::forEach(fn () => $urlValidation),
            'callbackUrls.*' => Rule::forEach(fn () => $urlValidation),
            'logoutUrls.*' => Rule::forEach(fn () => $urlValidation),
        ];
    }
}
