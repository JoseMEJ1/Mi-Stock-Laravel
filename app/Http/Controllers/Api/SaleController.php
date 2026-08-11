<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSaleRequest;
use App\Models\ProductBranch;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class SaleController extends CrudController
{
    protected string $modelClass = Sale::class;
    protected bool $tenantScoped = true;

    public function store(StoreSaleRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $data['total'] = collect($items)->sum(fn ($item) => $item['quantity'] * $item['price']);
        $data['user_id'] = $user->getKey();
        $data['tenant_id'] = $this->tenantIdFromUser($user);
        $data['sold_at'] = $data['sold_at'] ?? now();

        $sale = Sale::create($data);

        foreach ($items as $item) {
            SaleItem::create(array_merge($item, [
                'sale_id' => $sale->getKey(),
                'tenant_id' => $data['tenant_id'],
                'total' => $item['quantity'] * $item['price'],
            ]));
            if (!empty($data['branch_id'])) {
                // delegate stock decrement to event listeners
                try {
                    event(new \App\Events\ProductStockUpdated((string)$item['product_id'], (string)$data['branch_id'], -(float)$item['quantity'], ['sale_item' => ['quantity' => $item['quantity'], 'price' => $item['price'], 'sale_id' => (string)$sale->getKey()]]));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Dispatching ProductStockUpdated failed: '.$e->getMessage());
                }
            }
        }

        $this->logAction($user, 'created sale', $sale, $sale->toArray());

        // dispatch SaleCreated event for listeners (invoice generation, analytics)
        try {
            event(new \App\Events\SaleCreated($sale->toArray()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dispatching SaleCreated failed: '.$e->getMessage());
        }

        $this->broadcastModelChange($sale, 'created');

        return $this->success($sale->load('items'), 'Sale created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $sale = $this->tenantQuery($request)->find($id);
        if (!$sale) {
            return $this->error('Sale not found.', 404);
        }

        $data = $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'string'],
            'sold_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);
        $items = $data['items'];
        unset($data['items']);

        $data['total'] = collect($items)->sum(fn ($item) => $item['quantity'] * $item['price']);
        $sale->fill($data);
        $sale->save();

        SaleItem::where('sale_id', $sale->getKey())->delete();
        foreach ($items as $item) {
            SaleItem::create(array_merge($item, ['sale_id' => $sale->getKey(), 'total' => $item['quantity'] * $item['price']]));
        }

        $this->logAction($user, 'updated sale', $sale, $sale->toArray());
        $this->broadcastModelChange($sale, 'updated');

        return $this->success($sale->load('items'), 'Sale updated.');
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $sale = $this->tenantQuery($request)->find($id);
        if (!$sale) {
            return $this->error('Sale not found.', 404);
        }

        return $this->success($sale->load('items'));
    }
}
