<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = public_path('js/ph-json/province.json');

        if (! File::exists($path)) {
            $path = public_path('build/assets/js/ph-json/province.json');
        }

        if (! File::exists($path)) {
            $this->command->error("province.json file not found at: {$path}");

            return;
        }

        $json = File::get($path);
        $provinces = json_decode($json, true);

        if (! is_array($provinces)) {
            return;
        }

        $data = [];

        foreach ($provinces as $prov) {
            $data[] = [
                'province_code' => $prov['province_code'] ?? null,
                'province_name' => $prov['province_name'] ?? null,
                'region_code' => $prov['region_code'] ?? null,
            ];
        }

        foreach (array_chunk($data, 500) as $chunk) {
            DB::table('ref_provinces')->insertOrIgnore($chunk);
        }
    }
}
