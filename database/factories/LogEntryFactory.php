<?php

namespace Database\Factories;

use App\Models\LogEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogEntryFactory extends Factory
{
    protected $model = LogEntry::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['created','updated','deleted','login','logout']),
            'auditable_type' => null,
            'auditable_id' => null,
            'data' => ['info' => $this->faker->sentence()],
        ];
    }
}
