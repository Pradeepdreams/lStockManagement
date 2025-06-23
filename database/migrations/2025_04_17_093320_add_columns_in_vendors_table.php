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
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('tds_detail_id')->nullable()->after('gst_registration_type_id')->constrained()->onDelete('set null');
            $table->foreignId('group_id')->nullable()->after('vendor_code')->constrained()->onDelete('set null');
            $table->string('credit_limit')->nullable()->after('credit_days');
            $table->string('bank_name')->nullable()->after('ifsc_code');
            $table->string('bank_branch_name')->nullable()->after('bank_name');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['tds_detail_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn('tds_detail_id');
            $table->dropColumn('group_id');
            $table->dropColumn('credit_limit');
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_branch_name');
        });
    }
};
