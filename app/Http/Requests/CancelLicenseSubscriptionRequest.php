<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelLicenseSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
