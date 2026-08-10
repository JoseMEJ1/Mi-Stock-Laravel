<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersSeeder extends Seeder
{
    public function run()
    {
        Supplier::factory()->count(10)->create();
    }
}
