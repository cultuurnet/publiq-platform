<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class UpdateIntegrationUrlsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $urlValidation = [
            'id' => ['required', 'string'],
            'url' => ['required_without:environment', 'url:https', 'max:255'],
            'environment' => ['required_without:url', new Enum(Environment::class)],
        ];

        return [
            'loginUrl' => $urlValidation,
            'callbackUrls.*' => Rule::forEach(fn () => $urlValidation),
            'logoutUrls.*' => Rule::forEach(fn () => $urlValidation),
        ];
    }
}
