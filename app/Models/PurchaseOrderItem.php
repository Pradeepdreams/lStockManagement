<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{

    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'quantity',
        'inward_quantity',
        'pending_quantity',
        'item_status',
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
        'overall_item_price',
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function images()
    {
        return $this->hasMany(PurchaseOrderItemImage::class);
    }
}
