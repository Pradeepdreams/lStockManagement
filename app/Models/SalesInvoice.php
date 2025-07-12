<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use SoftDeletes, LogModelChangesTrait, EncryptableIdTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'customer_id',
        'against_sales_order',
        'sales_order_id',
        'mode_of_delivery',
        'remarks',
        'sub_total',
        'discount',
        'discount_percent',
        'discounted_total',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'gst_total',
        'total_amount',
        'status',
        'created_by',
        'updated_by'
    ];

    // protected $casts = [
    //     'invoice_date' => 'date',
    // ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function gstDetails()
    {
        return $this->hasMany(SalesInvoiceGstDetail::class);
    }
}
