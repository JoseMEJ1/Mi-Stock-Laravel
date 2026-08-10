<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Client;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;

class DemoWeekSeeder extends Seeder
{
    public function run()
    {
        // Ensure branches exist (create 6 total — 3 regular, 3 warehouses)
        $branches = Branch::factory()->count(6)->create()->each(function($b, $i){
            if ($i >= 3) {
                $b->name = 'Almacén ' . ($i - 2);
                $b->is_main = false;
                $b->save();
            } else {
                $b->name = 'Sucursal ' . ($i + 1);
                if ($i === 0) {
                    $b->is_main = true;
                }
                $b->save();
            }
        });

        // Create 20 users with varied roles and api tokens
        $roles = ['Administrador','Gerente','Operador','Vendedor','Soporte'];
        $users = collect();
        for ($i = 1; $i <= 20; $i++) {
            $email = "demo{$i}@mistock.local";
            $u = User::where('email', $email)->first();
            if (!$u) {
                $u = User::factory()->create([
                    'name' => 'Demo User ' . $i,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'api_token' => Str::random(60),
                ]);
            } else {
                // ensure token exists
                $u->api_token = $u->api_token ?? Str::random(60);
                $u->save();
            }

            try {
                $u->role = $roles[array_rand($roles)];
                $u->save();
            } catch (\Throwable $e) {
                // ignore if role cannot be saved
            }

            $users->push($u);
        }

        // Attach products to branches with initial stock (if products exist)
        $products = Product::all();
        if ($products->count() > 0) {
            foreach ($products as $product) {
                // attach to between 1 and 3 branches
                $attachBranches = $branches->random(min(3, $branches->count()))->pluck('id');
                foreach ($attachBranches as $branchId) {
                    $stock = rand(5, 200);
                    // product branches pivot
                    try {
                        $product->branches()->attach($branchId, ['stock' => $stock, 'reserved' => rand(0,10)]);
                    } catch (\Throwable $e) {
                        // ignoring duplicate attach exceptions
                    }
                }
            }
        }

        // Create a week's worth of activity (7 days)
        $today = Carbon::now();
        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $day = $today->copy()->subDays($daysAgo);
            // random number of purchases and sales and stock movements
            $numPurchases = rand(3, 8);
            $numSales = rand(5, 12);
            $numMovements = rand(8, 20);

            for ($p = 0; $p < $numPurchases; $p++) {
                $supplier = Supplier::all()->random();
                $branch = $branches->random();
                $user = $users->random();

                $purchase = Purchase::factory()->create([
                    'supplier_id' => $supplier ? $supplier->id : null,
                    'branch_id' => $branch->id,
                    'user_id' => $user->id,
                    'status' => 'received',
                    'purchased_at' => $day->copy()->addMinutes(rand(60, 600)),
                ]);

                // create 1-5 stock movements representing items received
                $items = rand(1,5);
                for ($i = 0; $i < $items; $i++) {
                    $prod = $products->random();
                    $qty = rand(1,30);
                    StockMovement::factory()->create([
                        'product_id' => $prod->id,
                        'branch_id' => $branch->id,
                        'user_id' => $user->id,
                        'movement_type' => 'in',
                        'quantity' => $qty,
                        'cost' => rand(1,200),
                        'reference' => 'PO-' . strtoupper(Str::random(6)),
                        'created_at' => $day->copy()->addMinutes(rand(60, 600)),
                    ]);
                    // update pivot stock if exists
                    try {
                        $pb = \App\Models\ProductBranch::firstOrNew(['product_id' => $prod->id, 'branch_id' => $branch->id]);
                        $pb->stock = ($pb->stock ?? 0) + $qty;
                        $pb->reserved = $pb->reserved ?? 0;
                        $pb->save();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }

            for ($s = 0; $s < $numSales; $s++) {
                $client = Client::all()->random();
                $branch = $branches->random();
                $user = $users->random();

                $sale = Sale::factory()->create([
                    'client_id' => $client ? $client->id : null,
                    'branch_id' => $branch->id,
                    'user_id' => $user->id,
                    'status' => 'completed',
                    'sold_at' => $day->copy()->addMinutes(rand(60, 600)),
                ]);

                // create 1-5 stock movements representing items sold (out)
                $items = rand(1,4);
                for ($i = 0; $i < $items; $i++) {
                    $prod = $products->random();
                    $qty = rand(1,10);
                    StockMovement::factory()->create([
                        'product_id' => $prod->id,
                        'branch_id' => $branch->id,
                        'user_id' => $user->id,
                        'movement_type' => 'out',
                        'quantity' => $qty,
                        'cost' => rand(1,200),
                        'reference' => 'SO-' . strtoupper(Str::random(6)),
                        'created_at' => $day->copy()->addMinutes(rand(60, 600)),
                    ]);
                    // decrement pivot stock
                    try {
                        $pb = \App\Models\ProductBranch::firstOrNew(['product_id' => $prod->id, 'branch_id' => $branch->id]);
                        $pb->stock = max(0, ($pb->stock ?? 0) - $qty);
                        $pb->reserved = $pb->reserved ?? 0;
                        $pb->save();
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }

            // additional random stock adjustments / transfers
            for ($m = 0; $m < $numMovements; $m++) {
                $prod = $products->random();
                $branch = $branches->random();
                $user = $users->random();
                $type = $this->randomMovementType();
                $qty = rand(1,20);
                StockMovement::factory()->create([
                    'product_id' => $prod->id,
                    'branch_id' => $branch->id,
                    'user_id' => $user->id,
                    'movement_type' => $type,
                    'quantity' => $qty,
                    'cost' => rand(1,200),
                    'reference' => strtoupper(Str::random(8)),
                    'created_at' => $day->copy()->addMinutes(rand(1,1400)),
                ]);
            }
        }
    }

    private function randomMovementType()
    {
        return collect(['in','out','adjustment','transfer'])->random();
    }
}
