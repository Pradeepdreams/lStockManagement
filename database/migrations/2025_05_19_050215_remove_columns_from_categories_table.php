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
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('gst_percent');
            $table->dropColumn('applicable_date');
            $table->dropColumn('hsn_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
             $table->decimal('gst_percent', 5, 2)->nullable();
            $table->string('hsn_code')->nullable();
             $table->date("applicable_date")->nullable()->after("gst_percent");
        });
    }
};
