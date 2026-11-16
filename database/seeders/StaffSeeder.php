<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::create([
            'name'         => 'ali',
            'email'        => 'ali2@gmail.com',
            'password'     => bcrypt('12345678'),
            'phone_number' => '1434567890',
            'role'         => 'admin',
        ]);
    }
}
