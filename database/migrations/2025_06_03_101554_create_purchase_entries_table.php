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
        Schema::create('purchase_entries', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_entry_number')->nullable();
            $table->boolean('against_po')->nullable();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->cascadeOnDelete();
            $table->string('vendor_invoice_no')->nullable();
            $table->date('vendor_invoice_date')->nullable();
            $table->decimal('sub_total_amount', 15, 2)->nullable();
            $table->decimal('gst_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->foreignId('purchase_person_id')->nullable()->constrained('employees')->cascadeOnDelete();
            $table->string('mode_of_delivery')->nullable();
            $table->foreignId('logistic_id')->nullable()->constrained('logistics')->cascadeOnDelete();
            $table->text('vendor_invoice_image')->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_entries');
    }
};
