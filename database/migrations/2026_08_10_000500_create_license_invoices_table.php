<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id');
            $table->string('folio')->nullable();
            $table->string('series')->nullable();
            $table->string('rfc')->nullable();
            $table->string('business_name')->nullable();
            $table->string('concept')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->integer('tax_rate')->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency')->default('MXN');
            $table->string('cfdi_pdf_url')->nullable();
            $table->string('cfdi_xml_url')->nullable();
            $table->string('status')->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_invoices');
    }
};
