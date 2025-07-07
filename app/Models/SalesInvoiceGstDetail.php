<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceGstDetail extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'sales_invoice_id',
        'gst_percent',
        'igst_percent',
        'cgst_percent',
        'sgst_percent',
        'igst_amount',
        'cgst_amount',
        'sgst_amount',
        'gst_amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
