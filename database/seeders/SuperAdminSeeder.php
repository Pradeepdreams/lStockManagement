<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get the SuperAdmin role with sanctum guard
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'SuperAdmin', 'guard_name' => 'sanctum']
        );

        // Ensure all permissions use sanctum guard
        $allPermissions = Permission::where('guard_name', 'sanctum')->get();
        $superAdminRole->syncPermissions($allPermissions);

        // Create or get the Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('dreams@123'),
            ]
        );

        // Assign the role to the user using the sanctum guard
        $superAdmin->assignRole(Role::where('name', 'SuperAdmin')->where('guard_name', 'sanctum')->first());

        // Assign role for all branches
        $branches = DB::table('branches')->get();

        foreach ($branches as $branch) {
            DB::table('branch_user_role')->updateOrInsert(
                [
                    'user_id'   => $superAdmin->id,
                    'branch_id' => $branch->id,
                ],
                [
                    'role_id'    => $superAdminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Super Admin user seeded with all permissions using sanctum guard.');
    }
}
