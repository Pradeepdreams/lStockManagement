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
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('invoiced_quantity', 10, 2)->nullable();
            $table->decimal('pending_quantity', 10, 2)->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('igst_percent', 5, 2)->nullable();
            $table->decimal('cgst_percent', 5, 2)->nullable();
            $table->decimal('sgst_percent', 5, 2)->nullable();
            $table->decimal('igst_amount', 10, 2)->nullable();
            $table->decimal('cgst_amount', 10, 2)->nullable();
            $table->decimal('sgst_amount', 10, 2)->nullable();
            $table->decimal('item_gst_amount', 10, 2)->nullable();
            $table->decimal('item_price', 10, 2)->nullable();
            $table->decimal('total_item_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->decimal('discounted_amount', 10, 2)->nullable();
            $table->decimal('overall_item_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
