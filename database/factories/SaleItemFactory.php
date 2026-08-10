<?php

namespace Database\Factories;

use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition()
    {
        $qty = $this->faker->numberBetween(1, 10);
        $price = $this->faker->randomFloat(2, 1, 200);
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $qty,
            'price' => $price,
            'total' => $qty * $price,
        ];
    }
}
