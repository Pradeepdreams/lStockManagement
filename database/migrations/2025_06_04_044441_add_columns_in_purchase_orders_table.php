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
            $table->string('po_status')->nullable()->after('logistic_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('inward_quantity')->nullable()->after('quantity');
            $table->decimal('pending_quantity')->nullable()->after('inward_quantity');
            $table->boolean('item_status')->default(0)->after('pending_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('po_status');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('inward_quantity');
            $table->dropColumn('pending_quantity');
            $table->dropColumn('item_status');
        });
    }
};
