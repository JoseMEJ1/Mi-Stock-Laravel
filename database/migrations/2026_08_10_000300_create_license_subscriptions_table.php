<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('plan_id');
            $table->string('period');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->string('payment_method')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency')->default('MXN');
            $table->text('cancellation_reason')->nullable();
            $table->date('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_subscriptions');
    }
};
