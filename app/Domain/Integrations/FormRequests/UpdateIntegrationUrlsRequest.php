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

    /**
     * By default, the validation will return error keys containing the index position of the urls that do not conform to the validation rules.
     * e.g. 'urls.0.url'
     * In the frontend the urls order is changed because they are grouped by type and environment, thus we cannot map the index position to the
     * corresponding input field.
     * That is why we replace the default error keys by custom error keys containing the type, environment and url.
     */
    public function after(): array
    {

        return [
            function (Validator $validator): void {
                // get the errors array containing key/value pairs from error key to error message
                $errors = $validator->errors()->get('urls.*');
                // get the form data for the urls
                $data = $validator->getData()['urls'];

                foreach ($errors as $originalKey => $originalMessage) {
                    // get the index out of the original key
                    // e.g. 'urls.0.url' -> '0'
                    $index = explode('.', $originalKey)[1];

                    // get the url form value for the current index
                    $url = $data[$index];

                    // create a custom error key, with the following structure 'type.environment.url'
                    $errorKey = "{$url['type']}.{$url['environment']}.{$url['url']}";

                    // create new error message where the prefix is replaced by 'Url'
                    $errorMessage = str_replace(
                        ucfirst($originalKey),
                        'Url',
                        $originalMessage
                    );

                    // add custom error
                    $validator->errors()->add($errorKey, $errorMessage);

                    // delete original error
                    $validator->errors()->forget($originalKey);
                }
            },
        ];
    }
}
