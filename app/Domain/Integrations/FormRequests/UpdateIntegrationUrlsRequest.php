<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Rules\UniqueIntegrationUrl;
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
            'urls.*' => Rule::forEach(fn () => [
                'id' => ['string'],
                'environment' => ['required', new Enum(Environment::class)],
                'type' => ['required', new Enum(IntegrationUrlType::class)],
                'url' => ['required', 'url:http,https', 'max:255', new UniqueIntegrationUrl($this->input('urls'))],
            ]),
        ];
    }

    /**
     * By default, the validation will return error keys containing the index position of the urls that do not conform to the validation rules.
     * e.g. 'urls.0.url'
     * In the frontend the urls order is changed because they are grouped by type and environment, thus we cannot map the index position to the
     * corresponding input field.
     * That is why we replace the default error keys by custom error keys containing the type, environment and url.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $errors = $validator->errors()->get('urls.*');
            /** @var array $data */
            $data = $validator->getData()['urls'];
            $grouped = collect($data)->groupBy(fn ($url) => "{$url['type']}.{$url['environment']}");

            foreach ($errors as $originalKey => $originalMessage) {
                $index = explode('.', $originalKey)[1];
                $url = $data[$index];
                $hash = "{$url['type']}.{$url['environment']}";
                $groupedIndex = $grouped->get($hash)?->search($url);
                $groupedIndex = $groupedIndex !== false ? $groupedIndex : $index;

                $errorMessage = str_replace(
                    ucfirst($originalKey),
                    'Url',
                    $originalMessage
                );

                $validator->errors()->add("{$hash}.{$groupedIndex}", $errorMessage);
                $validator->errors()->forget($originalKey);
            }
        });
    }
}
