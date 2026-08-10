<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventorySnapshot;

class InventorySnapshotsSeeder extends Seeder
{
    public function run()
    {
        InventorySnapshot::factory()->count(5)->create();
    }
}
