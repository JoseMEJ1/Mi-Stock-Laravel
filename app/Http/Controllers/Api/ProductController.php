<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends CrudController
{
    protected string $modelClass = Product::class;

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $product = Product::create($request->validated());
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

        $product->fill($request->validated());
        $product->save();
        $this->logAction($user, 'updated product', $product, $product->toArray());
        $this->broadcastModelChange($product, 'updated');

        return $this->success($product, 'Product updated.');
    }
}
