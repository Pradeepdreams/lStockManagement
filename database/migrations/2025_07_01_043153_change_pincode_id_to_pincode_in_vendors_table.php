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
            $table->dropForeign(['pincode_id']);
            $table->renameColumn('pincode_id', 'pincode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('pincode', 'pincode_id');
            $table->foreign('pincode_id')->references('id')->on('pincodes');
        });
    }
};
