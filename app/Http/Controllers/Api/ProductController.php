<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends CrudController
{
    protected string $modelClass = Product::class;

    public function index(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $query = Product::query()->with(['category', 'branches']);

        if (!empty($user->tenant_id)) {
            $query->where('tenant_id', (string) $user->tenant_id);
        } else {
            $query->whereRaw('1 = 0');
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
        $payload['tenant_id'] = $user->tenant_id ?? null;
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

        $product = Product::find($id);
        if (!$product) {
            return $this->error('Product not found.', 404);
        }

        $payload = method_exists($request, 'validated') ? $request->validated() : $request->all();
        if (!empty($user->tenant_id)) {
            $payload['tenant_id'] = $user->tenant_id;
        }
        $product->fill($payload);
        $product->save();
        $this->logAction($user, 'updated product', $product, $product->toArray());
        $this->broadcastModelChange($product, 'updated');

        return $this->success($product, 'Product updated.');
    }
}
