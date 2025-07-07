<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes, LogModelChangesTrait, EncryptableIdTrait;

    protected $fillable = [
        'sales_order_number',
        'order_date',
        'customer_id',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'gst_amount',
        'order_amount',
        'total_amount',
        'discount',
        'discount_percent',
        'discount_amount',
        'discounted_total',
        'payment_terms_id',
        'mode_of_delivery',
        'expected_delivery_date',
        'logistic_id',
        'sales_status',
        'remarks',
        'created_by',
        'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function logistic()
    {
        return $this->belongsTo(Logistic::class);
    }

    public function gstDetails()
    {
        return $this->hasMany(SalesOrderGstDetail::class);
    }
}
