<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use App\Domain\UdbUuid;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationUdbOrganizersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'organizers' => ['required', 'array'],
            'organizers.*.name' => ['required', 'string'],
            // Not Laravel's `uuid` rule: that one rejects the legacy UDB format
            // with the missing fourth hyphen, which a lot of organizers still use.
            'organizers.*.id' => ['required', 'string', 'regex:' . UdbUuid::UUID_REGEX],
        ];
    }
}
