<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Core seeders
        $this->call([
            CategoriesSeeder::class,
            SuppliersSeeder::class,
            BranchesSeeder::class,
            ClientsSeeder::class,
            ProductsSeeder::class,
            PurchasesSeeder::class,
            SalesSeeder::class,
            StockMovementsSeeder::class,
            InventorySnapshotsSeeder::class,
            LogsSeeder::class,
            \Database\Seeders\DemoWeekSeeder::class,
        ]);
    }
}
