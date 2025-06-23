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
        Schema::create('purchase_order_gst_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('purchase_order_gst_entries');
    }
};


// [
//     {
//             "gst_percent" : "10",
//             "igst_percent" : "10",
//             "cgst_percent" : "",
//             "sgst_percent" : "",
//             "igst_amount" : "1000",
//             "cgst_amount" : "",
//             "sgst_amount" : "",
//     },
// {

//             "gst_percent" : "20",
//             "igst_percent" : "",
//             "cgst_percent" : "10",
//             "sgst_percent" : "10",
//             "igst_amount" : "",
//             "cgst_amount" : "500",
//             "sgst_amount" : "500",
// }
// ]
