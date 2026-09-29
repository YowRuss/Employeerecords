<?php

namespace Database\Seeders;

use App\Models\IncomeType;
use Illuminate\Database\Seeder;

class IncomeTypeSeeder extends Seeder
{
    /**
     * Seed standard DepEd allowances and bonus income types.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'PERA',
                'description' => 'Personnel Economic Relief Allowance — standard monthly government allowance.',
                'default_amount' => 2000.00,
            ],
            [
                'name' => 'Mid-Year Bonus',
                'description' => 'Mid-Year Bonus equivalent to one month basic salary. Amount varies per employee.',
                'default_amount' => 0.00,
            ],
            [
                'name' => 'Cash Gift',
                'description' => 'Year-end cash gift for government employees.',
                'default_amount' => 5000.00,
            ],
            [
                'name' => 'Clothing Allowance',
                'description' => 'Annual clothing allowance for government employees.',
                'default_amount' => 6000.00,
            ],
            [
                'name' => 'Performance-Based Bonus',
                'description' => 'Performance-based incentive tied to OPCRF/IPCRF ratings.',
                'default_amount' => 0.00,
            ],
            [
                'name' => 'Year-End Bonus',
                'description' => 'Year-end bonus equivalent to one month basic salary.',
                'default_amount' => 0.00,
            ],
            [
                'name' => 'Productivity Enhancement Incentive',
                'description' => 'PEI — annual productivity incentive for government employees.',
                'default_amount' => 5000.00,
            ],
        ];

        foreach ($types as $type) {
            IncomeType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
