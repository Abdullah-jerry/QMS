<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $MOHRE = Department::where('name', 'MOHRE')->first();
        $DED = Department::where('name', 'DED')->first();
        $amer = Department::where('name', 'AMER')->first();

        if ($MOHRE) {
            Service::firstOrCreate([
                'name' => 'MOHRE',
                'department_id' => $MOHRE->id,
            ], [
                'prefix' => 'M',
                'status' => true,
            ]);
        }

        if ($DED) {
             Service::firstOrCreate([
                'name' => 'DED',
                'department_id' => $DED->id,
            ], [
                'prefix' => 'D',
                'status' => true,
            ]);
        }

        if ($amer) {
             Service::firstOrCreate([
                'name' => 'AMER',
                'department_id' => $amer->id,
            ], [
                'prefix' => 'A',
                'status' => true,
            ]);
        }
    }
}
