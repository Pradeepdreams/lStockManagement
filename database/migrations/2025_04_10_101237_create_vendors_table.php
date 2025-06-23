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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('vendor_code');
            $table->string('gst_in')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('area_id');
            $table->text('address')->nullable();
            $table->string('pincode')->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained();
            $table->integer('credit_days')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->boolean('transport_facility_provided')->default(false);
            $table->text('remarks')->nullable();
            $table->string('referred_source_type')->nullable();
            $table->unsignedBigInteger('referred_source_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
