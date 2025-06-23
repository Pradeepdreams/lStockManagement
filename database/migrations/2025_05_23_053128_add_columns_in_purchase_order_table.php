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
            $table->foreignId('logistic_id')->nullable()->after('expected_delivery_date')->constrained('logistics')->nullOnDelete();
            $table->decimal('igst_amount', 15, 2)->nullable()->after('vendor_id');
            $table->decimal('cgst_amount', 15, 2)->nullable()->after('igst_amount');
            $table->decimal('sgst_amount', 15, 2)->nullable()->after('cgst_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $dropForeigns = ['logistic_id'];
            $dropColumns = ['logistic_id', 'igst_amount', 'cgst_amount', 'sgst_amount'];

            $table->dropForeign($dropForeigns);
            $table->dropColumn($dropColumns);
        });
    }
};
