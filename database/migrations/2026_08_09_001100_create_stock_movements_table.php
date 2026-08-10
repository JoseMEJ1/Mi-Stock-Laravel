<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('branch_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('movement_type'); // in, out, adjustment, transfer
            $table->integer('quantity')->default(0);
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
