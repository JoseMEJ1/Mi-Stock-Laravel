<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicensePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'max_users' => ['sometimes', 'required', 'integer', 'min:1'],
            'max_branches' => ['sometimes', 'required', 'integer', 'min:1'],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_semester' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_annual' => ['sometimes', 'required', 'numeric', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
