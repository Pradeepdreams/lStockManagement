<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['name', 'area_code'];

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
}
