<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicensePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'max_users' => ['required', 'integer', 'min:1'],
            'max_branches' => ['required', 'integer', 'min:1'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_semester' => ['required', 'numeric', 'min:0'],
            'price_annual' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
