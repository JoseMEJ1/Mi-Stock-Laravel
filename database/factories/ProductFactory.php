<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $category = Category::all()->random()?->id;
        $supplier = Supplier::all()->random()?->id;

        return [
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'category_id' => $category ?? Category::factory(),
            'supplier_id' => $supplier ?? Supplier::factory(),
            'cost' => $this->faker->randomFloat(2, 1, 100),
            'price' => $this->faker->randomFloat(2, 1, 200),
            'unit' => 'pcs',
            'barcode' => $this->faker->unique()->ean13(),
        ];
    }
}
