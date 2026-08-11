<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreStockMovementRequest;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends CrudController
{
    protected string $modelClass = StockMovement::class;
    protected bool $tenantScoped = true;

    public function store(StoreStockMovementRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $movement = StockMovement::create(array_merge($request->validated(), [
            'user_id' => $user->getKey(),
            'tenant_id' => $this->tenantIdFromUser($user),
        ]));
        $this->logAction($user, 'created stock movement', $movement, $movement->toArray());

        // dispatch product stock updated event (listeners will adjust ProductBranch stock)
        try {
            $delta = $movement->quantity;
            $type = strtolower($movement->movement_type ?? 'entry');
            if (in_array($type, ['exit', 'salida', 'out', 'subtract'])) {
                $delta = -abs($movement->quantity);
            }
            event(new \App\Events\ProductStockUpdated((string)$movement->product_id, (string)$movement->branch_id, (float)$delta, ['movement_id' => (string)$movement->getKey(), 'type' => $movement->movement_type]));
        } catch (\Throwable $e) {
            // non-fatal - log and continue
            \Illuminate\Support\Facades\Log::warning('Dispatching ProductStockUpdated failed: '.$e->getMessage());
        }

        $this->broadcastModelChange($movement, 'created');

        return $this->success($movement, 'Stock movement created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $movement = $this->tenantQuery($request)->find($id);
        if (!$movement) {
            return $this->error('Stock movement not found.', 404);
        }

        $data = $request->validate([
            'product_id' => ['required', 'string'],
            'branch_id' => ['nullable', 'string'],
            'movement_type' => ['required', 'string', 'in:in,out,adjustment,transfer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $data['tenant_id'] = $this->tenantIdFromUser($user);
        $data['user_id'] = $user->getKey();
        $movement->fill($data);
        $movement->save();
        $this->logAction($user, 'updated stock movement', $movement, $movement->toArray());
        $this->broadcastModelChange($movement, 'updated');

        return $this->success($movement, 'Stock movement updated.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $movement = $this->tenantQuery($request)->find($id);
        if (!$movement) {
            return $this->error('Stock movement not found.', 404);
        }

        $payload = $movement->toArray();
        $movement->delete();
        $this->broadcastResourceChange($this->resourceNameFromInstance($movement), 'deleted', $payload);

        return $this->success(null, 'Stock movement deleted.');
    }
}
