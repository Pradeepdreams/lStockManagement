<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'pincode',
        'city',
        'country',
        'state',
    ];

    public function vendors(){
        return $this->hasMany(Vendor::class);
    }
}
