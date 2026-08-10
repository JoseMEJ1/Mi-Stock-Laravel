<?php

namespace Database\Factories;

use App\Models\InventorySnapshot;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventorySnapshotFactory extends Factory
{
    protected $model = InventorySnapshot::class;

    public function definition()
    {
        return [
            'branch_id' => Branch::factory(),
            'taken_by' => User::factory(),
            'snapshot_at' => now(),
            'data' => ['sample' => true],
        ];
    }
}
