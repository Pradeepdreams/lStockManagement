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
            $table->boolean('gst_applicable')->default(0)->after('credit_days');
            $table->date('gst_applicable_from')->nullable()->after('gst_applicable');
            $table->foreignId('gst_registration_type_id')->after('gst_applicable_from')->nullable()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('gst_applicable');
            $table->dropColumn('gst_applicable_from');
            $table->dropForeign(['gst_registration_type_id']);
            $table->dropColumn('gst_registration_type_id');
        });
    }
};
