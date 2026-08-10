<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category_id')->nullable();
            $table->string('supplier_id')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();
            $table->sparse_and_unique('sku');
            $table->sparse_and_unique('barcode');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
