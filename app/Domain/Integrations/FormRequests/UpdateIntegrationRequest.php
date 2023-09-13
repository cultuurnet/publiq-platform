<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'integrationName' => ['string', 'max:255'],
            'description' => ['string', 'max:255'],
        ];
    }
}
