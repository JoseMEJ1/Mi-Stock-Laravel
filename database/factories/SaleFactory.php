<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        return [
            'reference' => strtoupper($this->faker->bothify('SO-#####')),
            'client_id' => Client::factory(),
            'branch_id' => Branch::factory(),
            'user_id' => User::factory(),
            'total' => 0,
            'status' => 'draft',
            'sold_at' => null,
        ];
    }
}
