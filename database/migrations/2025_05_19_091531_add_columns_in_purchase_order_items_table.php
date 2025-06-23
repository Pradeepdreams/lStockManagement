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
        Schema::table('purchase_order_items', function (Blueprint $table) {
             $table->renameColumn('purchase_price', 'item_price');
            $table->string('hsn_code')->nullable()->after('quantity');
            $table->string('gst_percent')->nullable()->after('hsn_code');
            $table->decimal('gst_amount', 15, 2)->nullable()->after('gst_percent');
            $table->decimal('total_item_price', 15, 2)->nullable()->after('item_price');
            $table->decimal('overall_item_price', 15, 2)->nullable()->after('total_item_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $dropColumns = ['hsn_code', 'gst_percent', 'gst_amount', 'total_item_price', 'overall_item_price'];
            $table->dropColumn($dropColumns);
            $table->renameColumn('item_price', 'purchase_price');
        });
    }
};
