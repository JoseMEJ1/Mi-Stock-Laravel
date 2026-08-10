<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'reference' => strtoupper($this->faker->bothify('PO-#####')),
            'supplier_id' => Supplier::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'total' => 0,
            'status' => 'pending',
            'purchased_at' => null,
        ];
    }
}
