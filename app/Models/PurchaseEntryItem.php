<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class PurchaseEntryItem extends Model
{

     use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'purchase_entry_id',
        'po_item_id',
        'vendor_item_name',
        'item_id',
        'gst_percent',
        'hsn_code',
        'po_quantity',
        'quantity',
        'po_price',
        'vendor_price',
        // 'selling_price',
        'sub_total_amount',
        'total_amount',
    ];


    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
