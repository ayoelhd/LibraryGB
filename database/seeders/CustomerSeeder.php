<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name'         => 'omar',
            'phone_number' => '0912345678',
            'password'     => bcrypt('12345678'),
            'address'      => 'Benghazi',
        ]);
    }
}
