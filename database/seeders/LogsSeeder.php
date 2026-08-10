<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LogEntry;

class LogsSeeder extends Seeder
{
    public function run()
    {
        LogEntry::factory()->count(20)->create();
    }
}
