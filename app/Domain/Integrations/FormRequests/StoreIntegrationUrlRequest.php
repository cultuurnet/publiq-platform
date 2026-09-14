<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Rules\IntegrationUrlUniqueness;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreIntegrationUrlRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $unique = new IntegrationUrlUniqueness($this->route('id'), $this->input('environment'));

        return [
            'environment' => ['required', new Enum(Environment::class)],
            'type' => [
                'required',
                new Enum(IntegrationUrlType::class),
                $unique->singleLoginUrl(),
            ],
            'url' => ['required', 'url:http,https', 'max:255'],
        ];
    }
}
