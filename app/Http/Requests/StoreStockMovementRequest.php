<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
            'branch_id' => ['nullable', 'string'],
            'movement_type' => ['required', 'string', 'in:in,out,adjustment,transfer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }
}
