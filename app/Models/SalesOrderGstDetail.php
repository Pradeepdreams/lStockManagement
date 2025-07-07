<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderGstDetail extends Model
{
    use HasFactory, LogModelChangesTrait, EncryptableIdTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'sales_order_id', 'gst_percent', 'igst_percent', 'cgst_percent', 'sgst_percent',
        'igst_amount', 'cgst_amount', 'sgst_amount'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
