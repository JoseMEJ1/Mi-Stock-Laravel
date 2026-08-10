<?php

namespace App\Http\Controllers\Api;

use App\Events\ResourceChanged;
use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class ApiController extends Controller
{
    protected function user(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return null;
        }

        return User::where('api_token', $token)->first();
    }

    protected function authorize(Request $request)
    {
        $user = $this->user($request);

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $user;
    }

    protected function authorizeAdmin(Request $request)
    {
        $user = $this->authorize($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        if (!$this->isAdmin($user)) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        return $user;
    }

    protected function isAdmin(?User $user): bool
    {
        return $user && in_array($user->role, ['admin', 'super-admin'], true);
    }

    protected function resourceNameFromModel(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass));
    }

    protected function resourceNameFromInstance($model): string
    {
        return Str::snake(class_basename($model));
    }

    protected function broadcastResourceChange(string $resource, string $action, array $data): void
    {
        event(new ResourceChanged($resource, $action, $data));
    }

    protected function broadcastModelChange($model, string $action): void
    {
        $this->broadcastResourceChange($this->resourceNameFromInstance($model), $action, $model->toArray());
    }

    protected function success($data = null, string $message = 'OK', int $status = 200)
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message = 'Error', int $status = 400)
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }

    protected function logAction(User $user, string $action, $auditable = null, array $data = []): void
    {
        LogEntry::create([
            'user_id' => $user->getKey(),
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'data' => $data,
        ]);
    }

    protected function generateToken(): string
    {
        return Str::random(80);
    }
}
