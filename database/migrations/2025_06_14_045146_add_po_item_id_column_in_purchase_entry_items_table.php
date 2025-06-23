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
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->foreignId('po_item_id')->nullable()->after('purchase_entry_id')->constrained('purchase_order_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropForeign(['po_item_id']);
            $table->dropColumn('po_item_id');
        });
    }
};
