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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('gst_in')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('phone_no');
            $table->string('email')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->onDelete('set null');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode')->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained()->onDelete('set null');
            $table->string('credit_days')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->boolean('gst_applicable')->default(false);
            $table->foreignId('gst_registration_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('tds_detail_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
