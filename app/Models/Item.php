<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'item_name',
        'item_code',
        'category_id',
        'reorder_level',
        'unit_of_measurement'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryAttributeValues()
    {
        return $this->hasMany(ItemCategoryAttributeValue::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_item', 'vendor_id', 'item_id');
    }



    public function itemCategoryAttributeValues()
    {
        return $this->hasMany(ItemCategoryAttributeValue::class);
    }
}
