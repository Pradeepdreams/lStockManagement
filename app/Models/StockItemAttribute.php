<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class StockItemAttribute extends Model
{
    use LogModelChangesTrait, EncryptableIdTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'stock_item_id',
        'attribute_id',
        'attribute_name',
        'attribute_value_id',
        'attribute_value_name'
    ];

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
