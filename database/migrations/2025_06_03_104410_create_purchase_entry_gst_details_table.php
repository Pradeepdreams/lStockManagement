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
        Schema::create('purchase_entry_gst_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('gst_percent')->nullable();
            $table->string('igst_percent')->nullable();
            $table->string('cgst_percent')->nullable();
            $table->string('sgst_percent')->nullable();
            $table->string('igst_amount')->nullable();
            $table->string('cgst_amount')->nullable();
            $table->string('sgst_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_entry_gst_details');
    }
};
