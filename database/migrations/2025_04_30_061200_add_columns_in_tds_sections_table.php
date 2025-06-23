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
        Schema::table('tds_sections', function (Blueprint $table) {
            $table->integer('percent_with_pan')->nullable()->after('name');
            $table->integer('percent_without_pan')->nullable()->after('percent_with_pan');
            $table->integer('amount_limit')->nullable()->after('percent_without_pan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tds_sections', function (Blueprint $table) {
            $table->dropColumn(['percent_with_pan', 'percent_without_pan', 'amount_limit']);
        });
    }
};
