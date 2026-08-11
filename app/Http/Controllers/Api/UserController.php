<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends CrudController
{
    protected string $modelClass = User::class;
    protected bool $tenantScoped = true;

    public function store(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'in:user,admin,super-admin'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'user';
        $data['tenant_id'] = $this->tenantIdFromUser($user);

        $created = User::create($data);
        $this->logAction($user, 'created user', $created, $created->toArray());
        $this->broadcastModelChange($created, 'created');

        return $this->success($created, 'User created.', 201);
    }

    public function update(Request $request, $id)
    {
        $user = $this->authorize($request);
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $target = $this->tenantQuery($request)->find($id);
        if (!$target) {
            return $this->error('User not found.', 404);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'in:user,admin,super-admin'],
        ]);

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['role'] = $data['role'] ?? $target->role;
        $data['tenant_id'] = $this->tenantIdFromUser($user);
        $target->fill($data);
        $target->save();

        $this->logAction($user, 'updated user', $target, $target->toArray());
        $this->broadcastModelChange($target, 'updated');

        return $this->success($target, 'User updated.');
    }
}
