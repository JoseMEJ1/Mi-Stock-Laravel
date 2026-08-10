<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\PurchaseItem;

class PurchasesSeeder extends Seeder
{
    public function run()
    {
        Purchase::factory()->count(10)->create()->each(function($purchase){
            PurchaseItem::factory()->count(3)->create(['purchase_id' => $purchase->id]);
        });
    }
}
