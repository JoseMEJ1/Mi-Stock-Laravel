<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\LogEntry;

class LogUserLogin
{
    public function handle(UserLoggedIn $event): void
    {
        try {
            Log::info('User logged in', ['user_id' => $event->user?->_id ?? $event->user->id ?? null, 'timestamp' => $event->timestamp]);

            if (class_exists(LogEntry::class)) {
                $user = $event->user?->_id ?? $event->user->id ?? null;
                $tenantId = null;
                if ($user) {
                    $model = User::where('_id', $user)->orWhere('id', $user)->first();
                    $tenantId = $model?->tenant_id ? (string) $model->tenant_id : null;
                }

                LogEntry::create([
                    'user_id' => $user,
                    'tenant_id' => $tenantId,
                    'action' => 'user.login',
                    'auditable_type' => 'user',
                    'auditable_id' => $user,
                    'data' => ['timestamp' => $event->timestamp],
                ]);
            }
        } catch (\Throwable $e) {
            // Swallow to avoid breaking request flow
            Log::warning('LogUserLogin failed: '.$e->getMessage());
        }
    }
}
