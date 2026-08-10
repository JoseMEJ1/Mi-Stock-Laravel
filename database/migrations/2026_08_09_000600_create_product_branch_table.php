<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBranchTable extends Migration
{
    public function up()
    {
        Schema::create('product_branch', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('branch_id');
            $table->integer('stock')->default(0);
            $table->integer('reserved')->default(0);
            $table->timestamps();
            $table->unique(['product_id','branch_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_branch');
    }
}
