<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsSection extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'percent_with_pan',
        'percent_without_pan',
        'applicable_date',
        'amount_limit',
    ];

    public function tdsDetails()
    {
        return $this->hasMany(TdsDetail::class);
    }
}
