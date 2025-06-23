<?php

namespace App\Models;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->guard_name)) {
                $model->guard_name = 'sanctum';
            }
        });
    }


    public function branchUsers()
    {
        return $this->belongsToMany(User::class, 'branch_user_role')
            ->withPivot('user_id', 'branch_id')
            ->withTimestamps();
    }
}
