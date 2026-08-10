<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->sparse_and_unique('reference');
            $table->string('supplier_id')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('user_id')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
