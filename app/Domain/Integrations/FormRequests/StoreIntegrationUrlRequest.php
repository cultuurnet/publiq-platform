<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class StoreIntegrationUrlRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'environment' => ['required', new Enum(Environment::class)],
            'type' => [
                'required',
                new Enum(IntegrationUrlType::class),
                Rule::unique('integrations_urls', 'type')->where(function ($query) {
                    return $query->where('integration_id', $this->route('id'))
                        ->where('environment', $this->input('environment'))
                        ->where('type', IntegrationUrlType::Login->value);
                }),
            ],
            'url' => ['required', 'url:http,https', 'max:255'],
        ];
    }
}
