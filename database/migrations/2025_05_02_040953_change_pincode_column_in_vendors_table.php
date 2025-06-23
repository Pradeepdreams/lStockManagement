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

       

            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();

            $table->dropColumn('pincode');

            $table->unsignedBigInteger('pincode_id')->nullable();
            $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['pincode_id']);
            $table->dropColumn('pincode_id');

            $table->dropColumn(['country', 'state', 'city']);


            $table->string('pincode')->nullable();
        });
    }
};
