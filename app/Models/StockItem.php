<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use SoftDeletes, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'purchase_entry_id',
        'item_id',
        'item_name',
        'category_id',
        'category_name',
        'item_code',
        'status'
    ];

    public function attributes()
    {
        return $this->hasMany(StockItemAttribute::class);
    }

    public function barcodes()
    {
        return $this->hasMany(StockItemBarcode::class);
    }

    public function prices()
    {
        return $this->hasMany(StockItemPrice::class);
    }
}
