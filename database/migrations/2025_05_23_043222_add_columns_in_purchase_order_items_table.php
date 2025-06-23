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
            $table->string('igst_percent')->nullable()->after('gst_percent');
            $table->string('cgst_percent')->nullable()->after('igst_percent');
            $table->string('sgst_percent')->nullable()->after('cgst_percent');
            $table->string('igst_amount')->nullable()->after('sgst_percent');
            $table->string('cgst_amount')->nullable()->after('igst_amount');
            $table->string('sgst_amount')->nullable()->after('cgst_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $dropColumns = ['igst_percent', 'cgst_percent', 'sgst_percent', 'igst_amount', 'cgst_amount', 'sgst_amount'];
        });
    }
};
