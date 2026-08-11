<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'categories',
            'suppliers',
            'branches',
            'clients',
            'purchases',
            'sales',
            'stock_movements',
            'inventory_snapshots',
            'logs',
            'product_branch',
            'purchase_items',
            'sale_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'tenant_id')) {
                    $table->string('tenant_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'categories',
            'suppliers',
            'branches',
            'clients',
            'purchases',
            'sales',
            'stock_movements',
            'inventory_snapshots',
            'logs',
            'product_branch',
            'purchase_items',
            'sale_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'tenant_id')) {
                    $table->dropColumn('tenant_id');
                }
            });
        }
    }
};
