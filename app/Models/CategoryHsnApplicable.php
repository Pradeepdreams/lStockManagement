<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryHsnApplicable extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];


    protected $fillable = [
        'category_id',
        'hsn_code',
        'applicable_date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
