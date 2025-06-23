<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get the Admin role with sanctum guard
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'sanctum']
        );

        // Create or get the Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('Admin@123'),
            ]
        );

        // Assign the Admin role to the user
        $admin->assignRole($adminRole);

        // Assign role to all branches (if using multi-branch structure)
        $branches = DB::table('branches')->get();

        foreach ($branches as $branch) {
            DB::table('branch_user_role')->updateOrInsert(
                [
                    'user_id'   => $admin->id,
                    'branch_id' => $branch->id,
                ],
                [
                    'role_id'    => $adminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Admin user seeded without permissions using sanctum guard.');
    }
}
