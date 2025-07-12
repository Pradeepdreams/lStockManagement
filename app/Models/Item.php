<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Carbon\Carbon;
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
        'unit_of_measurement',
        'item_type',
        'purchase_price',
        'selling_price',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // public function categoryAttributeValues()
    // {
    //     return $this->hasMany(ItemCategoryAttributeValue::class);
    // }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class);
    }



    // public function itemCategoryAttributeValues()
    // {
    //     return $this->hasMany(ItemCategoryAttributeValue::class);
    // }

    public function gst_percents()
    {
        return $this->hasMany(CategoryGstApplicable::class);
    }

    public function hsn_codes()
    {
        return $this->hasMany(CategoryHsnApplicable::class);
    }

    public function sac_code()
    {
        return $this->hasMany(CategorySacApplicable::class);
    }

    public function latestGstPercent()
    {
        return $this->hasOne(CategoryGstApplicable::class, 'item_id')->latestOfMany('created_at');
        // return $this->hasOne(CategoryGstApplicable::class, 'category_id')->latestOfMany('applicable_date');
    }

    public function latestHsnCode()
    {
        return $this->hasOne(CategoryHsnApplicable::class, 'item_id')->latestOfMany('created_at');
        // return $this->hasOne(CategoryHsnApplicable::class, 'category_id')->latestOfMany('applicable_date');
    }

    public function latestSacCode()
    {
        return $this->hasOne(CategorySacApplicable::class, 'item_id')->latestOfMany('created_at');
        // return $this->hasOne(CategorySacApplicable::class, 'category_id')->latestOfMany('applicable_date');
    }


    public function activeGstPercent()
    {
        return $this->hasOne(CategoryGstApplicable::class, 'item_id')
            ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
            ->orderByDesc('applicable_date');
    }

    public function activeHsnCode()
    {
        return $this->hasOne(CategoryHsnApplicable::class, 'item_id')
            ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
            ->orderByDesc('applicable_date');
    }

    public function activeSacCode()
    {
        return $this->hasOne(CategorySacApplicable::class, 'item_id')
            ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
            ->orderByDesc('applicable_date');
    }
}
