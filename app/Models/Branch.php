<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, EncryptableIdTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['name', 'location'];


}
