<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;
use App\Models\LogEntry;

class LogUserLogin
{
    public function handle(UserLoggedIn $event): void
    {
        try {
            Log::info('User logged in', ['user_id' => $event->user?->_id ?? $event->user->id ?? null, 'timestamp' => $event->timestamp]);

            if (class_exists(LogEntry::class)) {
                LogEntry::create([
                    'user_id' => $event->user?->_id ?? $event->user->id ?? null,
                    'action' => 'user.login',
                    'auditable_type' => 'user',
                    'auditable_id' => $event->user?->_id ?? $event->user->id ?? null,
                    'data' => ['timestamp' => $event->timestamp],
                ]);
            }
        } catch (\Throwable $e) {
            // Swallow to avoid breaking request flow
            Log::warning('LogUserLogin failed: '.$e->getMessage());
        }
    }
}
