<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends CrudController
{
    protected string $modelClass = Category::class;
    protected bool $tenantScoped = true;

    public function store(StoreCategoryRequest $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $category = Category::create($this->tenantPayload($request, $request->validated()));
        $this->logAction($user, 'created category', $category, $category->toArray());
        $this->broadcastModelChange($category, 'created');

        return $this->success($category, 'Category created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $category = Category::find($id);
        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        $category->fill($this->tenantPayload($request, $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ])));
        $category->save();
        $this->logAction($user, 'updated category', $category, $category->toArray());
        $this->broadcastModelChange($category, 'updated');

        return $this->success($category, 'Category updated.');
    }
}
