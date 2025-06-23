<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategoryAttributeValue extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['item_id', 'attribute_category_id', 'attribute_value_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function attributeCategory()
{
    return $this->belongsTo(AttributeCategory::class, 'attribute_category_id');
}

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
