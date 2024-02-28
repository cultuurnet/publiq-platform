<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
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
        return [
            'urls.*' => Rule::forEach(fn (array $value, string $attribute) => [
                'id' => ['string'],
                'environment' => ["required_without:$attribute.id", new Enum(Environment::class)],
                'type' => ["required_without:$attribute.id", new Enum(IntegrationUrlType::class)],
                'url' => ['required', 'url:https', 'max:255'],
            ]),
        ];
    }
}
