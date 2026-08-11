<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends CrudController
{
    protected string $modelClass = Branch::class;
    protected bool $tenantScoped = true;

    public function store(StoreBranchRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $branch = Branch::create($this->tenantPayload($request, $request->validated()));
        $this->logAction($user, 'created branch', $branch, $branch->toArray());
        $this->broadcastModelChange($branch, 'created');

        return $this->success($branch, 'Branch created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $branch = Branch::find($id);
        if (!$branch) {
            return $this->error('Branch not found.', 404);
        }

        $branch->fill($this->tenantPayload($request, $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_main' => ['nullable', 'boolean'],
        ])));
        $branch->save();
        $this->logAction($user, 'updated branch', $branch, $branch->toArray());
        $this->broadcastModelChange($branch, 'updated');

        return $this->success($branch, 'Branch updated.');
    }
}
