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
        Schema::create('purchase_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->nullable()->constrained('purchase_entries')->nullOnDelete();
            $table->string('vendor_item_name')->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->decimal('gst_percent', 5, 2)->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('po_quantity', 15, 2)->nullable();
            $table->decimal('quantity', 15, 2)->nullable();
            $table->decimal('po_price', 15, 2)->nullable();
            $table->decimal('vendor_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('sub_total_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_entry_items');
    }
};
