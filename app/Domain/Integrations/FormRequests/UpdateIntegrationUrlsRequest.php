<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [
            function (Validator $validator): void
            {
                $errors = $validator->errors()->get('urls.*');
                $data = $validator->getData()['urls'];

                foreach ($errors as $originalKey => $originalMessage) {
                    $index = explode('.', $originalKey)[1];
                    $url = $data[$index];
                    $errorKey = sprintf("%s.%s.%s", $url['type'], $url['environment'], $url['url']);
                    $errorMessage = str_replace(
                        ucfirst($originalKey),
                        "Url",
                        $originalMessage
                    );
                    $validator->errors()->add($errorKey, $errorMessage);
                    $validator->errors()->forget($originalKey);
                }
            }
        ];
    }
}
