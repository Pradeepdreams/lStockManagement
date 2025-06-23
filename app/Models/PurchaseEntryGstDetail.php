<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class PurchaseEntryGstDetail extends Model
{

    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

     protected $fillable = [
        'purchase_entry_id',
        'gst_percent',
        'igst_percent',
        'cgst_percent',
        'sgst_percent',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
    ];

    public function purchaseEntry()
    {
        return $this->belongsTo(PurchaseEntry::class);
    }
}
