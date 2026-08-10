<?php

namespace App\Listeners;

use App\Events\ProductStockUpdated;
use Illuminate\Support\Facades\Log;
use App\Models\LogEntry;

class SendLowStockNotification
{
    // threshold can be adjusted or read from config
    protected int $threshold = 5;

    public function handle(ProductStockUpdated $event): void
    {
        try {
            // If quantity is low after update, create a log entry indicating low stock
            if ($event->quantity <= $this->threshold) {
                $message = sprintf('Low stock for product %s on branch %s: %s', $event->productId, $event->branchId, $event->quantity);
                Log::info($message);

                if (class_exists(LogEntry::class)) {
                    LogEntry::create([
                        'user_id' => null,
                        'action' => 'notification.low_stock',
                        'auditable_type' => 'product',
                        'auditable_id' => $event->productId,
                        'data' => ['branch_id' => $event->branchId, 'quantity' => $event->quantity, 'message' => $message, 'meta' => $event->meta ?? null],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SendLowStockNotification failed: '.$e->getMessage());
        }
    }
}
