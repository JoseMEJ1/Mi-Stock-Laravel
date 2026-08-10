<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends CrudController
{
    protected string $modelClass = Supplier::class;

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $supplier = Supplier::create($request->validated());
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

        $supplier->fill($request->validated());
        $supplier->save();
        $this->logAction($user, 'updated supplier', $supplier, $supplier->toArray());
        $this->broadcastModelChange($supplier, 'updated');

        return $this->success($supplier, 'Supplier updated.');
    }
}
