<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $fillable = ['name', 'links'];
    protected $appends = ['id_crypt'];
}
