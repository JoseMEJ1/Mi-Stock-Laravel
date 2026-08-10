<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreInventorySnapshotRequest;
use App\Models\InventorySnapshot;
use Illuminate\Http\Request;

class InventorySnapshotController extends CrudController
{
    protected string $modelClass = InventorySnapshot::class;

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $snapshot = InventorySnapshot::create(array_merge($request->validated(), ['taken_by' => $user->getKey()]));
        $this->logAction($user, 'created inventory snapshot', $snapshot, $snapshot->toArray());
        $this->broadcastModelChange($snapshot, 'created');

        return $this->success($snapshot, 'Inventory snapshot created.', 201);
    }
}
