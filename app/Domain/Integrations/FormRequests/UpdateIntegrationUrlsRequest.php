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
            'integrationName' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'loginUrls.*' => Rule::forEach(fn () => $urlValidation),
            'callbackUrls.*' => Rule::forEach(fn () => $urlValidation),
            'logoutUrls.*' => Rule::forEach(fn () => $urlValidation),
            'newIntegrationUrls.*' => Rule::forEach(fn () => [
                'id' => ['required', 'string'],
                'url' => ['required', 'url:https'],
            ]),
            ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $urlTypes = ['loginUrls', 'callbackUrls', 'logoutUrls', 'newIntegrationUrls'];

                foreach($urlTypes as $type) {
                    $failing = $validator->errors()->get("$type.*");
                    $values = $validator->getData()[$type];


                    foreach (array_keys($failing) as $errorMessage) {
                        $index = (int) explode('.', $errorMessage)[1];
                        $failingValue = $values[$index];

                        $validator->errors()->add($type . '.' . $failingValue['id'] . '.url', 'Requested URL is not valid');
                    }
                }
            },
        ];
    }
}
