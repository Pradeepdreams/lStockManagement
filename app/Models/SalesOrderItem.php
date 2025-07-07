<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'sales_order_id',
        'item_id',
        'quantity',
        'invoiced_quantity',
        'pending_quantity',
        'status',
        'hsn_code',
        'gst_percent',
        'igst_percent',
        'cgst_percent',
        'sgst_percent',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'item_gst_amount',
        'item_price',
        'total_item_price',
        'discount_price',
        'discounted_amount',
        'overall_item_price'
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
