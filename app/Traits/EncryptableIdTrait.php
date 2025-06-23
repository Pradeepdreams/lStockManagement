<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptableIdTrait
{
    public function getIdCryptAttribute()
    {
        return Crypt::encryptString($this->id);
    }
}
