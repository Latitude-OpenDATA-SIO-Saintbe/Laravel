<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        try {
            // Create permissions
            $permissions = [
                'create data',
                'edit data',
                'delete data',
                'view data',
                'can invite',
                'edit profile',
                'view dashboard',
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            // Create roles and assign permissions
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminRole->givePermissionTo($permissions);

            $managerRole = Role::firstOrCreate(['name' => 'manager']);
            $managerRole->givePermissionTo(['create data', 'edit data', 'view data', 'edit profile', 'view dashboard']);

            $moderateurRole = Role::firstOrCreate(['name' => 'moderateur']);
            $moderateurRole->givePermissionTo(['view data', 'edit profile', 'view dashboard']);

            $userRole = Role::firstOrCreate(['name' => 'user']);
            $userRole->givePermissionTo(['view data']);

            echo "Roles and permissions have been seeded successfully.\n";  // Add some success output
        } catch (\Exception $e) {
            echo "Error during seeding: " . $e->getMessage() . "\n";  // Catch and output errors
        }
    }
}

/**
 * Seeder class to create roles and permissions.
 *
 * This seeder will create the following permissions:
 * - create data
 * - edit data
 * - delete data
 * - view data
 * - can invite
 * - edit profile
 * - view dashboard
 *
 * It will also create the following roles and assign the respective permissions:
 * - admin: All permissions
 * - manager: create data, edit data, view data, edit profile, view dashboard
 * - moderateur: view data, edit profile, view dashboard
 * - user: view data
 *
 * If any errors occur during the seeding process, they will be caught and displayed.
 */
