<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIntegrationUrlRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'environment' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
        ];
    }
}
