<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchesSeeder extends Seeder
{
    public function run()
    {
        Branch::factory()->count(3)->create();
    }
}
