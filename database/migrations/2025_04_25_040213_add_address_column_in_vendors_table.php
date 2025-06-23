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
            $table->renameColumn('address', 'address_line_1')->nullable();
            $table->string('address_line_2')->nullable()->after('address_line_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('address_line_1', 'address')->nullable();
            $table->dropColumn('address_line_2');
        });
    }
};
