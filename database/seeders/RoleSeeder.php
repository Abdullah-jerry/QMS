<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view reports',
            'export reports',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage departments',
            'manage services',
            'issue tokens',
            'manage counters',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // Admin
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Reception
        $receptionRole = Role::create(['name' => 'reception']);
        $receptionRole->givePermissionTo([
            'issue tokens',
            'view reports',
        ]);

        // Counter User
        $counterUserRole = Role::create(['name' => 'counter_user']);
        $counterUserRole->givePermissionTo([
            'manage counters',
        ]);
        
        // Manager (Example)
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view reports',
            'export reports',
            'view users',
            'manage departments',
            'manage services',
        ]);
    }
}
