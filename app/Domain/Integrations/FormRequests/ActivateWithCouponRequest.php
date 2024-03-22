<?php

declare(strict_types=1);

namespace App\Domain\Integrations\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

// @deprecated
final class ActivateWithCouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'coupon' => ['required', 'string', 'max:255'],
        ];
    }
}
