<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_invoice_gst_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->nullable();
            $table->decimal('cgst_percent', 5, 2)->nullable();
            $table->decimal('sgst_percent', 5, 2)->nullable();
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_gst_details');
    }
};
