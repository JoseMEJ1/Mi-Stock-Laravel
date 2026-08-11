<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Client;
use App\Models\InventorySnapshot;
use App\Models\LicenseTenant;
use App\Models\LogEntry;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManuelMonthSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = LicenseTenant::updateOrCreate(
            ['email' => 'manuel@gmail.com'],
            [
                'name' => 'Manuel Operaciones',
                'commercial_name' => 'Manuel Operaciones',
                'rfc' => 'MANO860811XXX',
                'status' => 'active',
                'period' => 'monthly',
            ]
        );

        $tenantId = (string) $tenant->getKey();

        $user = User::updateOrCreate(
            ['email' => 'manuel@gmail.com'],
            [
                'name' => 'Manuel Pérez',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'tenant_id' => $tenantId,
                'api_token' => Str::random(80),
            ]
        );

        $existingHistory = Purchase::where('tenant_id', $tenantId)->count()
            + Sale::where('tenant_id', $tenantId)->count()
            + StockMovement::where('tenant_id', $tenantId)->count();

        if ($existingHistory > 0) {
            return;
        }

        $categories = collect([
            ['name' => 'Tecnología', 'slug' => 'tecnologia'],
            ['name' => 'Accesorios', 'slug' => 'accesorios'],
            ['name' => 'Papelería', 'slug' => 'papeleria'],
            ['name' => 'Consumibles', 'slug' => 'consumibles'],
        ])->map(function (array $data) use ($tenantId) {
            return Category::updateOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['name'] . ' para operación mensual',
                ]
            );
        });

        $suppliers = collect([
            ['name' => 'Distribuidora Manuel', 'code' => 'SUP-MAN-01'],
            ['name' => 'Tech Supply MX', 'code' => 'SUP-MAN-02'],
            ['name' => 'Office Pro', 'code' => 'SUP-MAN-03'],
        ])->map(function (array $data) use ($tenantId) {
            return Supplier::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $data['code']],
                [
                    'name' => $data['name'],
                    'email' => Str::slug($data['name'], '.') . '@mail.com',
                    'phone' => '555-010-' . random_int(10, 99),
                ]
            );
        });

        $branches = collect([
            ['name' => 'Matriz Manuel', 'code' => 'BR-MAN-01', 'is_main' => true],
            ['name' => 'Sucursal Centro', 'code' => 'BR-MAN-02', 'is_main' => false],
            ['name' => 'Almacén Principal', 'code' => 'BR-MAN-03', 'is_main' => false],
        ])->map(function (array $data) use ($tenantId) {
            return Branch::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $data['code']],
                [
                    'name' => $data['name'],
                    'address' => $data['name'] . ' - Dirección operativa',
                    'phone' => '555-100-' . random_int(10, 99),
                    'is_main' => $data['is_main'],
                ]
            );
        });

        $clients = collect(range(1, 8))->map(function (int $index) use ($tenantId) {
            return Client::updateOrCreate(
                ['tenant_id' => $tenantId, 'email' => "cliente{$index}.manuel@gmail.com"],
                [
                    'name' => "Cliente Manuel {$index}",
                    'phone' => '555-200-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'address' => 'Cliente operativo ' . $index,
                    'tax_id' => 'CLM' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                ]
            );
        });

        $productTemplates = [
            ['sku' => 'MAN-001', 'name' => 'Laptop Administrativa', 'cost' => 12500, 'price' => 16900],
            ['sku' => 'MAN-002', 'name' => 'Tableta Comercial', 'cost' => 5200, 'price' => 7900],
            ['sku' => 'MAN-003', 'name' => 'Escáner Bluetooth', 'cost' => 980, 'price' => 1890],
            ['sku' => 'MAN-004', 'name' => 'Impresora de Etiquetas', 'cost' => 1850, 'price' => 3200],
            ['sku' => 'MAN-005', 'name' => 'Smartwatch Inventario', 'cost' => 1450, 'price' => 2490],
            ['sku' => 'MAN-006', 'name' => 'Alexa Punto de Venta', 'cost' => 890, 'price' => 1590],
            ['sku' => 'MAN-007', 'name' => 'Paquete de Etiquetas', 'cost' => 120, 'price' => 290],
            ['sku' => 'MAN-008', 'name' => 'Consumible Multiuso', 'cost' => 240, 'price' => 520],
        ];

        $products = collect($productTemplates)->values()->map(function (array $data, int $index) use ($tenantId, $categories, $suppliers, $branches) {
            $product = Product::updateOrCreate(
                ['tenant_id' => $tenantId, 'sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'description' => 'Producto demo de 1 mes para Manuel',
                    'category_id' => $categories[$index % $categories->count()]->getKey(),
                    'supplier_id' => $suppliers[$index % $suppliers->count()]->getKey(),
                    'cost' => $data['cost'],
                    'price' => $data['price'],
                    'unit' => 'pz',
                    'barcode' => '84' . str_pad((string) (100000000000 + $index), 11, '0', STR_PAD_LEFT),
                ]
            );

            $mainBranch = $branches[0];
            ProductBranch::updateOrCreate(
                ['tenant_id' => $tenantId, 'product_id' => $product->getKey(), 'branch_id' => $mainBranch->getKey()],
                [
                    'stock' => random_int(35, 90),
                    'reserved' => random_int(0, 5),
                ]
            );

            return $product;
        });

        $stockByProduct = [];
        foreach ($products as $product) {
            $pb = ProductBranch::where('tenant_id', $tenantId)
                ->where('product_id', $product->getKey())
                ->first();
            $stockByProduct[(string) $product->getKey()] = (int) ($pb->stock ?? 0);
        }

        $start = Carbon::now()->subDays(29)->startOfDay();
        for ($dayOffset = 0; $dayOffset < 30; $dayOffset++) {
            $day = $start->copy()->addDays($dayOffset);
            $dayTime = $day->copy()->addHours(9 + ($dayOffset % 8));

            $purchase = Purchase::create([
                'tenant_id' => $tenantId,
                'reference' => 'PO-MAN-' . strtoupper(Str::random(6)),
                'supplier_id' => $suppliers->random()->getKey(),
                'branch_id' => $branches[0]->getKey(),
                'user_id' => $user->getKey(),
                'total' => 0,
                'status' => 'received',
                'purchased_at' => $dayTime->copy()->addMinutes(15),
                'created_at' => $dayTime,
                'updated_at' => $dayTime,
            ]);

            $purchaseItems = collect();
            $purchaseTotal = 0;
            foreach (collect($products->random(random_int(1, 3))) as $product) {
                $quantity = random_int(3, 12);
                $cost = (float) $product->cost;
                $item = PurchaseItem::create([
                    'tenant_id' => $tenantId,
                    'purchase_id' => $purchase->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => $quantity,
                    'cost' => $cost,
                    'total' => $quantity * $cost,
                    'created_at' => $dayTime,
                    'updated_at' => $dayTime,
                ]);
                $purchaseItems->push($item);
                $purchaseTotal += $item->total;

                $stockByProduct[(string) $product->getKey()] = ($stockByProduct[(string) $product->getKey()] ?? 0) + $quantity;
                ProductBranch::updateOrCreate(
                    ['tenant_id' => $tenantId, 'product_id' => $product->getKey(), 'branch_id' => $branches[0]->getKey()],
                    ['stock' => $stockByProduct[(string) $product->getKey()], 'reserved' => 0]
                );

                StockMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->getKey(),
                    'branch_id' => $branches[0]->getKey(),
                    'user_id' => $user->getKey(),
                    'movement_type' => 'in',
                    'quantity' => $quantity,
                    'cost' => $cost,
                    'reference' => $purchase->reference,
                    'note' => 'Entrada por compra automática',
                    'created_at' => $dayTime->copy()->addMinutes(20),
                    'updated_at' => $dayTime->copy()->addMinutes(20),
                ]);
            }
            $purchase->update(['total' => $purchaseTotal]);

            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'reference' => 'SO-MAN-' . strtoupper(Str::random(6)),
                'client_id' => $clients->random()->getKey(),
                'branch_id' => $branches[0]->getKey(),
                'user_id' => $user->getKey(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => $dayTime->copy()->addHours(3),
                'created_at' => $dayTime->copy()->addHours(3),
                'updated_at' => $dayTime->copy()->addHours(3),
            ]);

            $saleTotal = 0;
            foreach (collect($products->random(random_int(1, 3))) as $product) {
                $available = max(1, (int) ($stockByProduct[(string) $product->getKey()] ?? 10));
                $quantity = random_int(1, min(5, $available));
                $price = (float) $product->price;
                SaleItem::create([
                    'tenant_id' => $tenantId,
                    'sale_id' => $sale->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                    'created_at' => $dayTime->copy()->addHours(3),
                    'updated_at' => $dayTime->copy()->addHours(3),
                ]);

                $stockByProduct[(string) $product->getKey()] = max(0, ($stockByProduct[(string) $product->getKey()] ?? 0) - $quantity);
                ProductBranch::updateOrCreate(
                    ['tenant_id' => $tenantId, 'product_id' => $product->getKey(), 'branch_id' => $branches[0]->getKey()],
                    ['stock' => $stockByProduct[(string) $product->getKey()], 'reserved' => 0]
                );

                $saleTotal += $quantity * $price;

                StockMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->getKey(),
                    'branch_id' => $branches[0]->getKey(),
                    'user_id' => $user->getKey(),
                    'movement_type' => 'out',
                    'quantity' => $quantity,
                    'cost' => $product->cost,
                    'reference' => $sale->reference,
                    'note' => 'Salida por venta automática',
                    'created_at' => $dayTime->copy()->addHours(3)->addMinutes(10),
                    'updated_at' => $dayTime->copy()->addHours(3)->addMinutes(10),
                ]);
            }
            $sale->update(['total' => $saleTotal]);

            $adjustmentProduct = $products->random();
            $adjustmentQuantity = random_int(1, 4);
            $adjustmentType = random_int(0, 1) ? 'in' : 'adjustment';
            $adjustmentDelta = $adjustmentType === 'in' ? $adjustmentQuantity : -$adjustmentQuantity;
            $stockByProduct[(string) $adjustmentProduct->getKey()] = max(0, ($stockByProduct[(string) $adjustmentProduct->getKey()] ?? 0) + $adjustmentDelta);
            ProductBranch::updateOrCreate(
                ['tenant_id' => $tenantId, 'product_id' => $adjustmentProduct->getKey(), 'branch_id' => $branches[0]->getKey()],
                ['stock' => $stockByProduct[(string) $adjustmentProduct->getKey()], 'reserved' => 0]
            );

            StockMovement::create([
                'tenant_id' => $tenantId,
                'product_id' => $adjustmentProduct->getKey(),
                'branch_id' => $branches[0]->getKey(),
                'user_id' => $user->getKey(),
                'movement_type' => $adjustmentType,
                'quantity' => $adjustmentQuantity,
                'cost' => $adjustmentProduct->cost,
                'reference' => 'ADJ-' . strtoupper(Str::random(5)),
                'note' => 'Ajuste operativo automático',
                'created_at' => $dayTime->copy()->addHours(6),
                'updated_at' => $dayTime->copy()->addHours(6),
            ]);

            LogEntry::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->getKey(),
                'action' => 'user.activity',
                'auditable_type' => 'daily_session',
                'auditable_id' => null,
                'data' => [
                    'date' => $day->toDateString(),
                    'purchase_ref' => $purchase->reference,
                    'sale_ref' => $sale->reference,
                ],
                'created_at' => $dayTime->copy()->addHours(7),
                'updated_at' => $dayTime->copy()->addHours(7),
            ]);

            if (($dayOffset + 1) % 7 === 0) {
                $snapshotData = [];
                foreach ($products as $product) {
                    $snapshotData[] = [
                        'product_id' => (string) $product->getKey(),
                        'name' => $product->name,
                        'stock' => $stockByProduct[(string) $product->getKey()] ?? 0,
                    ];
                }

                InventorySnapshot::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branches[0]->getKey(),
                    'taken_by' => $user->getKey(),
                    'snapshot_at' => $dayTime->copy()->addHours(8),
                    'data' => $snapshotData,
                    'created_at' => $dayTime->copy()->addHours(8),
                    'updated_at' => $dayTime->copy()->addHours(8),
                ]);
            }
        }
    }
}
