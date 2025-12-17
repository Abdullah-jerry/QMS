<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view admin dashboard',
            'view reception dashboard',
            'view counter dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage departments',
            'manage counters',
            'issue tokens',
            'call tokens',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        
        // Admin
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Reception
        $receptionRole = Role::firstOrCreate(['name' => 'reception']);
        $receptionRole->givePermissionTo([
            'view reception dashboard',
            'issue tokens',
        ]);

        // Counter Staff
        $counterRole = Role::firstOrCreate(['name' => 'counter_user']);
        $counterRole->givePermissionTo([
            'view counter dashboard',
            'call tokens',
        ]);
    }
}
