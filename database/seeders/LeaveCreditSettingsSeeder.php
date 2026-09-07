<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveCreditSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('leave_credit_settings')->insertOrIgnore([
            [
                'employee_type' => 'GLOBAL',
                'setting_key' => 'certifying_officer_name',
                'setting_value' => 'RHEA MARIE A. ASUNCION',
                'description' => 'Name of the HR / Certifying Officer for forms',
            ],
            [
                'employee_type' => 'GLOBAL',
                'setting_key' => 'certifying_officer_position',
                'setting_value' => 'Administrative Officer IV',
                'description' => 'Position of the HR / Certifying Officer for forms',
            ],
        ]);
    }
}
