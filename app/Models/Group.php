<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $fillable = ['name', 'description', 'is_active'];

    // protected $fillable = ['name', 'description', 'parent_id', 'is_active'];

    // public function parent()
    // {
    //     return $this->belongsTo(Group::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(Group::class, 'parent_id');
    // }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
}
