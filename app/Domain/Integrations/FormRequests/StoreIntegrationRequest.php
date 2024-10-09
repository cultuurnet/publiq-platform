<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\Integrations\IntegrationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIntegrationRequest extends FormRequest
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
            'website' => ['required_if:integrationType,uitpas', 'nullable', 'url:http,https', 'max:255'],
            'firstNameFunctionalContact' => ['required', 'string', 'max:255'],
            'lastNameFunctionalContact' => ['required', 'string', 'max:255'],
            'emailFunctionalContact' => ['required', 'string', 'email:filter', 'max:255'],
            'firstNameTechnicalContact' => ['required', 'string', 'max:255'],
            'lastNameTechnicalContact' => ['required', 'string', 'max:255'],
            'emailTechnicalContact' => ['required', 'string', 'email:filter', 'max:255'],
            'agreement' => ['required', 'string'],
            'uitpasAgreement' => [Rule::requiredIf($this->input('integrationType') === IntegrationType::UiTPAS->value), 'nullable', 'string'],
            'coupon' => ['nullable', 'string'],
        ];
    }
}
