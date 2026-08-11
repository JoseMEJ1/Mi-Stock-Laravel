<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:20'],
            'plan_id' => ['nullable', 'string', 'max:255', 'required_with:period,payment_method'],
            'period' => ['nullable', 'string', 'in:monthly,semester,annual', 'required_with:plan_id,payment_method'],
            'payment_method' => ['nullable', 'string', 'max:100', 'required_with:plan_id,period'],
            'auto_renew' => ['nullable', 'boolean'],
        ];
    }
}
