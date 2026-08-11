<?php

namespace App\Listeners;

use App\Events\LicenseActivityEvent;
use App\Models\User;
use App\Models\LogEntry;

class LogLicenseActivity
{
    public function handle(LicenseActivityEvent $event): void
    {
        $tenantId = null;
        if ($event->userId) {
            $user = User::where('_id', $event->userId)->orWhere('id', $event->userId)->first();
            $tenantId = $user?->tenant_id ? (string) $user->tenant_id : null;
        }

        LogEntry::create([
            'user_id' => $event->userId,
            'tenant_id' => $tenantId,
            'action' => $event->action,
            'auditable_type' => $event->resource,
            'auditable_id' => $event->entityId,
            'data' => $event->data,
        ]);
    }
}
