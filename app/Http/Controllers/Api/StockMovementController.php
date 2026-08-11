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
}
