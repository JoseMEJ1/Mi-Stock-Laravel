<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string'],
            'plan_id' => ['required', 'string'],
            'period' => ['required', 'string', 'in:monthly,semester,annual'],
            'start_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'string'],
            'auto_renew' => ['nullable', 'boolean'],
        ];
    }
}
