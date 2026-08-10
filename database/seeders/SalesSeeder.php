<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;

class SalesSeeder extends Seeder
{
    public function run()
    {
        Sale::factory()->count(10)->create()->each(function($sale){
            SaleItem::factory()->count(2)->create(['sale_id' => $sale->id]);
        });
    }
}
