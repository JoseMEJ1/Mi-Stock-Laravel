<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\ProductBranch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;

class PurchaseController extends CrudController
{
    protected string $modelClass = Purchase::class;
    protected bool $tenantScoped = true;

    public function store(StorePurchaseRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $data['total'] = collect($items)->sum(fn ($item) => $item['quantity'] * $item['cost']);
        $data['user_id'] = $user->getKey();
        $data['tenant_id'] = $this->tenantIdFromUser($user);
        $data['purchased_at'] = $data['purchased_at'] ?? now();

        $purchase = Purchase::create($data);

        foreach ($items as $item) {
            $purchaseItem = PurchaseItem::create(array_merge($item, [
                'purchase_id' => $purchase->getKey(),
                'tenant_id' => $data['tenant_id'],
                'total' => $item['quantity'] * $item['cost'],
            ]));
            if (!empty($data['branch_id'])) {
                // delegate stock update to event listeners
                try {
                    event(new \App\Events\ProductStockUpdated((string)$item['product_id'], (string)$data['branch_id'], (float)$item['quantity'], ['purchase_item_id' => (string)$purchaseItem->getKey(), 'purchase_id' => (string)$purchase->getKey()]));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Dispatching ProductStockUpdated failed: '.$e->getMessage());
                }
            }
        }

        $this->logAction($user, 'created purchase', $purchase, $purchase->toArray());
        $this->broadcastModelChange($purchase, 'created');

        return $this->success($purchase->load('items'), 'Purchase created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $purchase = $this->tenantQuery($request)->find($id);
        if (!$purchase) {
            return $this->error('Purchase not found.', 404);
        }

        $data = $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'string'],
            'purchased_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost' => ['required', 'numeric', 'min:0'],
        ]);
        $items = $data['items'];
        unset($data['items']);

        $data['total'] = collect($items)->sum(fn ($item) => $item['quantity'] * $item['cost']);
        $purchase->fill($data);
        $purchase->save();

        PurchaseItem::where('purchase_id', $purchase->getKey())->delete();
        foreach ($items as $item) {
            PurchaseItem::create(array_merge($item, ['purchase_id' => $purchase->getKey(), 'total' => $item['quantity'] * $item['cost']]));
        }

        $this->logAction($user, 'updated purchase', $purchase, $purchase->toArray());
        $this->broadcastModelChange($purchase, 'updated');

        return $this->success($purchase->load('items'), 'Purchase updated.');
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $purchase = $this->tenantQuery($request)->find($id);
        if (!$purchase) {
            return $this->error('Purchase not found.', 404);
        }

        return $this->success($purchase->load('items'));
    }
}
