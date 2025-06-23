<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;

class TdsDetail extends Model
{

    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = [
        'name',
        'description',
        'tds_section_id',
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function tdsSection()
    {
        return $this->belongsTo(TdsSection::class);
    }
}
