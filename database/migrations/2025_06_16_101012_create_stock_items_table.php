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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable();
            $table->foreignId('purchase_entry_id')->nullable()->constrained('purchase_entries');
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->string('item_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->string('category_name')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
