<?php

namespace Database\Factories;

use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    public function definition()
    {
        $qty = $this->faker->numberBetween(1, 20);
        $cost = $this->faker->randomFloat(2, 1, 100);
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quantity' => $qty,
            'cost' => $cost,
            'total' => $qty * $cost,
        ];
    }
}
