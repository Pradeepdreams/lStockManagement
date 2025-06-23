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
            $table->integer("barcoded_quantity")->nullable()->after('total_amount');
            $table->integer("pending_quantity")->nullable()->after('barcoded_quantity');
            $table->boolean('status')->default(0)->after('pending_quantity');
            $table->dropColumn('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropColumn('barcoded_quantity');
            $table->dropColumn('pending_quantity');
            $table->dropColumn('status');
            $table->decimal('selling_price', 15, 2)->nullable()->after('vendor_price');
        });
    }
};
