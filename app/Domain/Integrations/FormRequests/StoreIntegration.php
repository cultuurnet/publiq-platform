<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIntegration extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'integrationType' => ['required', 'string'],
            'subscriptionId' => ['required', 'string'],
            'integrationName' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'firstNameFunctionalContact' => ['required', 'string', 'max:255'],
            'lastNameFunctionalContact' => ['required', 'string', 'max:255'],
            'emailFunctionalContact' => ['required', 'string', 'email', 'max:255'],
            'firstNameTechnicalContact' => ['required', 'string', 'max:255'],
            'lastNameTechnicalContact' => ['required', 'string', 'max:255'],
            'emailTechnicalContact' => ['required', 'string', 'email', 'max:255'],
            'agreement' => ['required', 'string'],
        ];
    }
}
