<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockMovement;

class StockMovementsSeeder extends Seeder
{
    public function run()
    {
        StockMovement::factory()->count(30)->create();
    }
}
