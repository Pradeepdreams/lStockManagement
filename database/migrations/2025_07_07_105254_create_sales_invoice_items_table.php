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
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('item_price', 12, 2)->default(0);
            $table->decimal('sub_total', 12, 2)->default(0);

            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('discounted_price', 12, 2)->default(0);

            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->nullable();
            $table->decimal('cgst_percent', 5, 2)->nullable();
            $table->decimal('sgst_percent', 5, 2)->nullable();

            $table->decimal('igst_amount', 12, 2)->nullable()->default(0);
            $table->decimal('cgst_amount', 12, 2)->nullable()->default(0);
            $table->decimal('sgst_amount', 12, 2)->nullable()->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('after_discount_total', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
    }
};
