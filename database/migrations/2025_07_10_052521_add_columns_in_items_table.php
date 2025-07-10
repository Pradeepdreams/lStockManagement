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
        Schema::table('items', function (Blueprint $table) {
            $table->string('item_type')->nullable()->after('unit_of_measurement');
            $table->decimal('purchase_price', 15, 2)->nullable()->after('item_type');
            $table->decimal('selling_price', 15, 2)->nullable()->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('item_type');
            $table->dropColumn('purchase_price');
            $table->dropColumn('selling_price');
        });
    }
};
