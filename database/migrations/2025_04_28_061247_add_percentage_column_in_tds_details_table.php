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
        Schema::table('tds_details', function (Blueprint $table) {
            $table->integer('percent_with_pan')->nullable()->after('percentage');
            $table->renameColumn('percentage', 'percent_without_pan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tds_details', function (Blueprint $table) {
            $table->dropColumn('percent_with_pan');
            $table->rename('percent_without_pan', 'percentage');
        });
    }
};
