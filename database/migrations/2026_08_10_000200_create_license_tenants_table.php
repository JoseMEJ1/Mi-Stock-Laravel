<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('commercial_name')->nullable();
            $table->string('rfc')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('fiscal_address')->nullable();
            $table->string('fiscal_regime')->nullable();
            $table->string('plan_id')->nullable();
            $table->string('period')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_tenants');
    }
};
