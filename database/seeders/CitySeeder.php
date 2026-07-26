<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = public_path('js/ph-json/city.json');

        if (! File::exists($path)) {
            $path = public_path('build/assets/js/ph-json/city.json');
        }

        if (! File::exists($path)) {
            $this->command->error("city.json file not found at: {$path}");

            return;
        }

        $json = File::get($path);
        $cities = json_decode($json, true);

        if (! is_array($cities)) {
            return;
        }

        $data = [];

        foreach ($cities as $city) {
            $data[] = [
                'city_code' => $city['city_code'] ?? null,
                'city_name' => $city['city_name'] ?? null,
                'province_code' => $city['province_code'] ?? null,
            ];
        }

        foreach (array_chunk($data, 500) as $chunk) {
            DB::table('ref_cities')->insertOrIgnore($chunk);
        }
    }
}
