<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = public_path('js/ph-json/region.json');

        if (! File::exists($path)) {
            $path = public_path('build/assets/js/ph-json/region.json');
        }

        if (! File::exists($path)) {
            $this->command->error("region.json file not found at: {$path}");

            return;
        }

        $json = File::get($path);
        $regions = json_decode($json, true);

        if (! is_array($regions)) {
            return;
        }

        $data = [];

        foreach ($regions as $region) {
            $data[] = [
                'region_code' => $region['region_code'] ?? null,
                'region_name' => $region['region_name'] ?? null,
            ];
        }

        foreach (array_chunk($data, 500) as $chunk) {
            DB::table('ref_regions')->insertOrIgnore($chunk);
        }
    }
}
