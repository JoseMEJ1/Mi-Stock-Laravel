<?php

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'movement_type' => $this->faker->randomElement(['in','out','adjustment','transfer']),
            'quantity' => $this->faker->numberBetween(1,50),
            'cost' => $this->faker->optional()->randomFloat(2,1,100),
            'reference' => strtoupper($this->faker->bothify('MOV-#####')),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
