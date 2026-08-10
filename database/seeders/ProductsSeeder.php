<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        Product::factory()->count(50)->create()->each(function($product){
            // Attach product to up to two random branches with initial stock
            $branches = \App\Models\Branch::all()->random(min(2, \App\Models\Branch::count()))->pluck('id');
            foreach($branches as $branchId){
                $product->branches()->attach($branchId, ['stock' => rand(0,100), 'reserved' => 0]);
            }
        });
    }
}
