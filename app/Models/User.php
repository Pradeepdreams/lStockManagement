<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\EncryptableIdTrait;
use App\Traits\LogModelChangesTrait;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, EncryptableIdTrait, LogModelChangesTrait;

    protected $appends = ['id_crypt'];

    protected $guard_name = 'sanctum';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function employee(){
        return $this->hasOne(Employee::class);
    }


    public function branchUserRoles(){
        return $this->hasMany(BranchUserRole::class);
    }

    public function branchRoles()
    {
        return $this->belongsToMany(Role::class, 'branch_user_role')
            ->withPivot('branch_id')
            ->withTimestamps();
    }

    public function assignBranchRoleById($roleId, $branchId)
    {
        DB::table('branch_user_role')->updateOrInsert(
            [
                'user_id'   => $this->id,
                'branch_id' => $branchId,
                'role_id'   => $roleId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function removeBranchRoleById($roleId, $branchId)
    {
        DB::table('branch_user_role')
            ->where('user_id', $this->id)
            ->where('branch_id', $branchId)
            ->where('role_id', $roleId)
            ->delete();
    }


    public function hasBranchRole(string $roleName, $branchId): bool
    {
        return $this->branchRoles()
            ->where('name', $roleName)
            ->wherePivot('branch_id', $branchId)
            ->exists();
    }



    public function hasBranchPermission($permission)
    {
        try {

            $branchId = app('currentBranchId');

            if (!$branchId) {
                return false;
            }

            if ($this->hasRole('SuperAdmin')) {
                return true;
            }

            $role = $this->branchRoles()->wherePivot('branch_id', $branchId)->first();

            if ($role && $role->hasPermissionTo($permission)) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
