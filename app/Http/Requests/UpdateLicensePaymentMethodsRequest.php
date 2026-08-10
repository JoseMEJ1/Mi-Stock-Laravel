<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicensePaymentMethodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'methods' => ['required', 'array'],
            'methods.*.enabled' => ['required', 'boolean'],
            'methods.*.commission' => ['required', 'numeric', 'min:0'],
        ];
    }
}
