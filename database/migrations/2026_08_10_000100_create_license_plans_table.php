<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('max_users')->default(1);
            $table->integer('max_branches')->default(1);
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_semester', 12, 2)->default(0);
            $table->decimal('price_annual', 12, 2)->default(0);
            $table->json('features')->nullable();
            $table->json('modules')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->sparse_and_unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_plans');
    }
};
