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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->integer('discount_percent')->nullable()->after('total_amount');
            $table->decimal('discount_amount', 15, 2)->nullable()->after('discount_percent');
            $table->decimal('discounted_total', 15, 2)->nullable()->after('discount_percent');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('discounted_amount', 15, 2)->nullable()->after('total_item_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
            $table->dropColumn('discount_amount');
            $table->dropColumn('discount_total');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('discounted_amount');
        });
    }
};
