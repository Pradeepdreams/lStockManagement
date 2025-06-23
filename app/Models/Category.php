<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'description',
        'margin_percent_from',
        'margin_percent_to',
        // 'gst_percent',
        // 'applicable_date',
        // 'hsn_code',
        'active_status'
    ];


    public function attributes()
    {
        return $this->belongsToMany(Attribute::class);
    }


    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function gst_percents()
    {
        return $this->hasMany(CategoryGstApplicable::class);
    }

    public function hsn_codes()
    {
        return $this->hasMany(CategoryHsnApplicable::class);
    }

    public function latestGstPercent()
    {
        return $this->hasOne(CategoryGstApplicable::class, 'category_id')->latestOfMany('created_at');
        // return $this->hasOne(CategoryGstApplicable::class, 'category_id')->latestOfMany('applicable_date');
    }

    public function latestHsnCode()
    {
        return $this->hasOne(CategoryHsnApplicable::class, 'category_id')->latestOfMany('created_at');
        // return $this->hasOne(CategoryHsnApplicable::class, 'category_id')->latestOfMany('applicable_date');
    }


    public function activeGstPercent()
    {
        return $this->hasOne(CategoryGstApplicable::class, 'category_id')
            ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
            ->orderByDesc('applicable_date');
    }

    public function activeHsnCode()
    {
        return $this->hasOne(CategoryHsnApplicable::class, 'category_id')
            ->where('applicable_date', '<=', Carbon::now()->format('Y-m-d'))
            ->orderByDesc('applicable_date');
    }
}
