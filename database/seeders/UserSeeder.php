<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'HR',
                'last_name' => 'Manager',
                'username' => 'hr_admin',
                'password' => Hash::make('password123'),
                'role_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'username' => 'employee1',
                'password' => Hash::make('password123'),
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
