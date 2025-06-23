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
            $table->date("applicable_date")->nullable()->after("percent_without_pan");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tds_sections', function (Blueprint $table) {
            $table->dropColumn("applicable_date");
        });
    }
};
