<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends CrudController
{
    protected string $modelClass = Product::class;
    protected bool $tenantScoped = true;

    public function index(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $query = Product::query()->with(['category', 'branches']);

        $tenantId = $this->tenantIdFromUser($user);
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        } else {
            $query->where('tenant_id', '__no_tenant__');
        }

        $items = $query->get()->map(function (Product $product) {
            $stock = (int) $product->branches->sum(function ($branch) {
                return (int) ($branch->pivot->stock ?? 0);
            });

            return array_merge($product->toArray(), [
                'category_name' => $product->category?->name,
                'stock' => $stock,
            ]);
        });

        return $this->success($items);
    }

    public function store(StoreProductRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $payload = $request->validated();
        $payload['tenant_id'] = $this->tenantIdFromUser($user);
        $product = Product::create($payload);
        $this->logAction($user, 'created product', $product, $product->toArray());

        // dispatch ProductCreated event for listeners
        try {
            event(new \App\Events\ProductCreated($product->toArray()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dispatching ProductCreated failed: '.$e->getMessage());
        }

        $this->broadcastModelChange($product, 'created');

        return $this->success($product, 'Product created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $product = $this->tenantQuery($request)->find($id);
        if (!$product) {
            return $this->error('Product not found.', 404);
        }

        $payload = $request->validate([
            'sku' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'string'],
            'supplier_id' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:255'],
        ]);
        $tenantId = $this->tenantIdFromUser($user);
        if ($tenantId) {
            $payload['tenant_id'] = $tenantId;
        }
        $product->fill($payload);
        $product->save();
        $this->logAction($user, 'updated product', $product, $product->toArray());
        $this->broadcastModelChange($product, 'updated');

        return $this->success($product, 'Product updated.');
    }
}
