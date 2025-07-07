<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use LogModelChangesTrait, EncryptableIdTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'sales_invoice_id',
        'item_id',
        'sales_order_item_id',
        'quantity',
        'item_price',
        'sub_total',
        'discount_percent',
        'discount_amount',
        'discounted_price',
        'gst_percent',
        'igst_percent',
        'cgst_percent',
        'sgst_percent',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'gst_amount',
        'total_amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }
}
