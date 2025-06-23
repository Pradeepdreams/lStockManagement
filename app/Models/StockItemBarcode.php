<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class StockItemBarcode extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];
     protected $fillable = [
        'stock_item_id',
        'barcode_value',
        'barcode_image',
        'item_index',
        'status'
    ];

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
