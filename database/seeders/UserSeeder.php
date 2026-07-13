<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'full_name' => 'System Administrator',
                'username'  => 'admin',
                'password'  => Hash::make('password123'), // Encrypted password
                'role_id'   => 3, // Admin
                'created_at'=> now(),
            ],
            [
                'full_name' => 'HR Manager',
                'username'  => 'hr_admin',
                'password'  => Hash::make('password123'),
                'role_id'   => 2, // HR
                'created_at'=> now(),
            ],
            [
                'full_name' => 'Juan Dela Cruz',
                'username'  => 'employee1',
                'password'  => Hash::make('password123'),
                'role_id'   => 1, // Employee
                'created_at'=> now(),
            ],
        ]);
    }
}