<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

abstract class CrudController extends ApiController
{
    protected string $modelClass;

    public function index(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $items = $this->modelClass::all();

        return $this->success($items);
    }

    public function show(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $model = $this->modelClass::find($id);

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

        $model = $this->modelClass::find($id);

        if (!$model) {
            return $this->error('Resource not found.', 404);
        }

        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();
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

        $model = $this->modelClass::find($id);

        if (!$model) {
            return $this->error('Resource not found.', 404);
        }

        $payload = $model->toArray();
        $model->delete();
        $this->broadcastResourceChange($this->resourceNameFromInstance($model), 'deleted', $payload);

        return $this->success(null, 'Resource deleted.');
    }
}
