<?php

namespace App\Listeners;

use App\Events\ProductStockUpdated;
use Illuminate\Support\Facades\Log;
use App\Models\ProductBranch;
use App\Models\Product;

class UpdateProductStock
{
    public function handle(ProductStockUpdated $event): void
    {
        try {
            // Find or create product branch record and adjust stock
            $productId = $event->productId;
            $branchId = $event->branchId;
            $quantity = $event->quantity;

            if (empty($productId) || empty($branchId)) {
                return;
            }

            // Ensure ProductBranch exists and update stock safely
            $pb = ProductBranch::firstOrCreate([
                'product_id' => $productId,
                'branch_id' => $branchId,
            ], [
                'stock' => 0,
                'reserved' => 0,
            ]);

            $current = (float) ($pb->stock ?? 0);
            $new = $current + $quantity;
            if ($new < 0) {
                $new = 0;
            }

            $pb->stock = $new;
            $pb->save();

            // Optionally update a cached aggregated stock on the product document
            if (class_exists(Product::class)) {
                $product = Product::find($productId);
                if ($product) {
                    // Best-effort: do not fail if the product lacks the field
                    try {
                        // compute simple aggregated stock across branches if necessary is out-of-scope here
                        // leave as no-op — listeners should be idempotent and non-breaking
                    } catch (\Throwable $inner) {
                        Log::debug('UpdateProductStock: failed to update aggregated stock: '.$inner->getMessage());
                    }
                }
            }

            Log::info('Updated product-branch stock', ['product_id' => $productId, 'branch_id' => $branchId, 'new_stock' => $new]);
        } catch (\Throwable $e) {
            Log::warning('UpdateProductStock failed: '.$e->getMessage());
        }
    }
}
