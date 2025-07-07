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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('set null');

            $table->string('mode_of_delivery')->nullable();
            $table->text('remarks')->nullable();

            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('discounted_total', 12, 2)->default(0);

            $table->decimal('igst_amount', 12, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            $table->decimal('gst_total', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('Draft');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
