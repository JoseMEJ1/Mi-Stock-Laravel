<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends CrudController
{
    protected string $modelClass = Supplier::class;
    protected bool $tenantScoped = true;

    public function store(StoreSupplierRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $supplier = Supplier::create($this->tenantPayload($request, $request->validated()));
        $this->logAction($user, 'created supplier', $supplier, $supplier->toArray());
        $this->broadcastModelChange($supplier, 'created');

        return $this->success($supplier, 'Supplier created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->error('Supplier not found.', 404);
        }

        $supplier->fill($this->tenantPayload($request, $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ])));
        $supplier->save();
        $this->logAction($user, 'updated supplier', $supplier, $supplier->toArray());
        $this->broadcastModelChange($supplier, 'updated');

        return $this->success($supplier, 'Supplier updated.');
    }
}
