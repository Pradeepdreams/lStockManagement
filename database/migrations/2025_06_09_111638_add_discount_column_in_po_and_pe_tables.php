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
            $table->integer('discount')->nullable()->after('order_amount');
        });

        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->integer('discount')->nullable()->after('sub_total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('discount');
        });

        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
