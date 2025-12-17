<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Counter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            // UserSeeder::class, // If you have one
        ]);

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@qms.local',
            'password' => Hash::make('Admin@123'),
            'is_active' => true,
        ]);
        $admin->assignSingleRole('admin');

        // Create Departments
        $mohre = Department::create([
            'name' => 'MOHRE',
            'code' => 'M',
            'description' => 'MOHRE SERVICES',
            'is_active' => true,
            'display_order' => 1,
        ]);
        $ded = Department::create([
            'name' => 'DED',
            'code' => 'D',
            'description' => 'DED SERVICES',
            'is_active' => true,
            'display_order' => 2,
        ]);
        $amer = Department::create([
            'name' => 'AMER',
            'code' => 'A',
            'description' => 'AMER SERVICES',
            'is_active' => true,
            'display_order' => 3,
        ]);



        // Create Counters
        Counter::create([
            'name' => 'Counter 1',
            'counter_number' => 'C1',
            'department_id' => $mohre->id,
            'is_active' => true,
        ]);

        Counter::create([
            'name' => 'Counter 2',
            'counter_number' => 'C2',
            'department_id' => $ded->id,
            'is_active' => true,
        ]);

        Counter::create([
            'name' => 'Counter 3',
            'counter_number' => 'C3',
            'department_id' => $amer->id,
            'is_active' => true,
        ]);


        // Create Reception User
        $receptionUser = User::create([
            'name' => 'Abdullah 1027',
            'email' => 'abdullah@qms.local',
            'password' => Hash::make('Abdullah@123'),
            'is_active' => true,
        ]);
        $receptionUser->assignSingleRole('counter_user');

        // Create Counter Users
        $dedUser = User::create([
            'name' => 'Hussain 1006',
            'email' => 'hussain@qms.local',
            'password' => Hash::make('Hussain@123'),
            'is_active' => true,
        ]);
        $dedUser->assignSingleRole('counter_user');

        // Assign Departments to Users
        $dedUser->departments()->attach($ded->id);
    }
}
