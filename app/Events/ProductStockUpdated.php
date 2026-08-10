<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductStockUpdated
{
    use Dispatchable, SerializesModels;

    public $productId;
    public $branchId;
    public $quantity;
    public $meta;
    public $timestamp;

    public function __construct(string $productId, string $branchId, float $quantity, array $meta = [])
    {
        $this->productId = $productId;
        $this->branchId = $branchId;
        $this->quantity = $quantity;
        $this->meta = $meta;
        $this->timestamp = now()->toISOString();
    }
}
