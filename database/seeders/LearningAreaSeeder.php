<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use Illuminate\Database\Seeder;

class LearningAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            'TLE - 6', 'TLE - 15', 'MATH', 'SCIENCE', 'ENGLISH', 'FILIPINO', 'ESP/VALUED ED', 'ARALING PANLIPUNAN',
        ];

        foreach ($areas as $area) {
            LearningArea::firstOrCreate(['name' => $area]);
        }
    }
}
