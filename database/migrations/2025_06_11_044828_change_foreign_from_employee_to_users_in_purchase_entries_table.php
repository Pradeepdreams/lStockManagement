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
        Schema::table('purchase_entries', function (Blueprint $table) {
              // Drop the old foreign key constraint
        $table->dropForeign(['purchase_person_id']);

        // Add new constraint referencing users table
        $table->foreign('purchase_person_id')
              ->references('id')
              ->on('users')
              ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
              $table->dropForeign(['purchase_person_id']);

        $table->foreign('purchase_person_id')
              ->references('id')
              ->on('employees')
              ->cascadeOnDelete();
        });
    }
};
