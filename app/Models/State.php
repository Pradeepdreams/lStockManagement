<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'country_id',
        'country_code',
        'fips_code',
        'iso2',
        'type',
        'level',
        'parent_id',
        'latitude',
        'longitude',
        'flag',
        'wikiDataId',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
