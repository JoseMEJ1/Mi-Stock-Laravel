<?php

namespace App\Listeners;

use App\Events\LicenseActivityEvent;
use App\Models\LogEntry;

class LogLicenseActivity
{
    public function handle(LicenseActivityEvent $event): void
    {
        LogEntry::create([
            'user_id' => $event->userId,
            'action' => $event->action,
            'auditable_type' => $event->resource,
            'auditable_id' => $event->entityId,
            'data' => $event->data,
        ]);
    }
}
