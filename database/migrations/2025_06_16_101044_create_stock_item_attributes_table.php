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
        Schema::create('stock_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items');
            $table->foreignId('attribute_id')->nullable()->constrained('attributes');
            $table->string('attribute_name')->nullable();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values');
            $table->string('attribute_value_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_item_attributes');
    }
};
