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
        Schema::table('discount_on_purchases', function (Blueprint $table) {
            $table->date('applicable_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_on_purchases', function (Blueprint $table) {
            $table->string('applicable_date')->nullable()->change();
        });
    }
};
