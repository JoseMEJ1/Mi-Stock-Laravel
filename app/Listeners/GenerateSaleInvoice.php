<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\LogEntry;

class GenerateSaleInvoice
{
    public function handle(SaleCreated $event): void
    {
        try {
            // For demo purposes: create a log entry indicating invoice generation
            $saleId = $event->sale['id'] ?? ($event->sale['_id'] ?? null);
            $message = 'Invoice generated for sale: '.$saleId;
            Log::info($message, ['sale' => $event->sale]);

            if (class_exists(LogEntry::class)) {
                $tenantId = null;
                $userId = $event->sale['user_id'] ?? null;
                if ($userId) {
                    $user = User::where('_id', $userId)->orWhere('id', $userId)->first();
                    $tenantId = $user?->tenant_id ? (string) $user->tenant_id : null;
                }

                LogEntry::create([
                    'user_id' => $userId,
                    'tenant_id' => $tenantId,
                    'action' => 'sale.invoice_generated',
                    'auditable_type' => 'sale',
                    'auditable_id' => $saleId,
                    'data' => ['sale' => $event->sale, 'message' => $message],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('GenerateSaleInvoice failed: '.$e->getMessage());
        }
    }
}
