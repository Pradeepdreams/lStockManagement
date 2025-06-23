<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderGstEntry extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['purchase_order_id', 'gst_percent', 'igst_percent', 'cgst_percent', 'sgst_percent', 'igst_amount', 'cgst_amount', 'sgst_amount'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
