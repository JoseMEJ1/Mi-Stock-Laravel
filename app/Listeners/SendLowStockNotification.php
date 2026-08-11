<?php

namespace App\Listeners;

use App\Events\ProductStockUpdated;
use App\Models\Branch;
use App\Models\Product;
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
                    $tenantId = null;
                    $product = Product::find($event->productId);
                    if ($product && !empty($product->tenant_id)) {
                        $tenantId = (string) $product->tenant_id;
                    }

                    if (!$tenantId && $event->branchId) {
                        $branch = Branch::find($event->branchId);
                        if ($branch && !empty($branch->tenant_id)) {
                            $tenantId = (string) $branch->tenant_id;
                        }
                    }

                    LogEntry::create([
                        'user_id' => null,
                        'tenant_id' => $tenantId,
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
