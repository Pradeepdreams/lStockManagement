<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryGstApplicable extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];


    protected $fillable = [
        'item_id',
        'gst_percent',
        'applicable_date',
    ];


    public function items()
    {
        return $this->belongsTo(Item::class);
    }
}
