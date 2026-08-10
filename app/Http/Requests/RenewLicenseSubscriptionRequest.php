<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewLicenseSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['nullable', 'string', 'in:monthly,semester,annual'],
            'payment_method' => ['required', 'string'],
        ];
    }
}
