<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'fiscal_address' => ['nullable', 'string'],
            'fiscal_regime' => ['nullable', 'string', 'max:10'],
            'plan_id' => ['nullable', 'string'],
            'period' => ['nullable', 'string', 'in:monthly,semester,annual'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
