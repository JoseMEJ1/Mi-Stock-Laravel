<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventorySnapshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'string'],
            'snapshot_at' => ['nullable', 'date'],
            'data' => ['nullable', 'array'],
        ];
    }
}
