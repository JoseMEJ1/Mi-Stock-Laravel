<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

abstract class CrudController extends ApiController
{
    protected string $modelClass;
    protected bool $tenantScoped = false;
    protected string $tenantColumn = 'tenant_id';

    protected function currentTenantId(Request $request): ?string
    {
        return $this->tenantIdFromUser($this->user($request));
    }

    protected function tenantQuery(Request $request)
    {
        $query = $this->modelClass::query();

        if (!$this->tenantScoped) {
            return $query;
        }

        $tenantId = $this->currentTenantId($request);
        if (!$tenantId) {
            return $query->where($this->tenantColumn, '__no_tenant__');
        }

        return $query->where($this->tenantColumn, $tenantId);
    }

    protected function tenantPayload(Request $request, array $data): array
    {
        if (!$this->tenantScoped) {
            return $data;
        }

        $tenantId = $this->currentTenantId($request);
        if ($tenantId) {
            $data[$this->tenantColumn] = $tenantId;
        }

        return $data;
    }

    public function index(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $items = $this->tenantQuery($request)->get();

        return $this->success($items);
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $model = $this->tenantQuery($request)->find($id);

        if (!$model) {
            return $this->error('Resource not found.', 404);
        }

        return $this->success($model);
    }

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();
        $data = $this->tenantPayload($request, $data);
        $model = $this->modelClass::create($data);
        $this->broadcastModelChange($model, 'created');

        return $this->success($model, 'Resource created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $model = $this->tenantQuery($request)->find($id);

        if (!$model) {
            return $this->error('Resource not found.', 404);
        }

        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();
        $data = $this->tenantPayload($request, $data);
        $model->fill($data);
        $model->save();
        $this->broadcastModelChange($model, 'updated');

        return $this->success($model, 'Resource updated.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $model = $this->tenantQuery($request)->find($id);

        if (!$model) {
            return $this->error('Resource not found.', 404);
        }

        $payload = $model->toArray();
        $model->delete();
        $this->broadcastResourceChange($this->resourceNameFromInstance($model), 'deleted', $payload);

        return $this->success(null, 'Resource deleted.');
    }
}
