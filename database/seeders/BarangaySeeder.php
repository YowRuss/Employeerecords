<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Increase memory limit for parsing large JSON file
        ini_set('memory_limit', '512M');

        $path = public_path('js/ph-json/barangay.json');

        if (! File::exists($path)) {
            $path = public_path('build/assets/js/ph-json/barangay.json');
        }

        if (! File::exists($path)) {
            $this->command->error("barangay.json file not found at: {$path}");

            return;
        }

        $json = File::get($path);
        $barangays = json_decode($json, true);

        if (! is_array($barangays)) {
            return;
        }

        $data = [];

        foreach ($barangays as $brgy) {
            $data[] = [
                'brgy_code' => $brgy['brgy_code'] ?? null,
                'brgy_name' => $brgy['brgy_name'] ?? null,
                'city_code' => $brgy['city_code'] ?? null,
            ];
        }

        unset($json, $barangays);

        // Chunk data into batches of 1,000 records using array_chunk & insertOrIgnore
        foreach (array_chunk($data, 1000) as $chunk) {
            DB::table('ref_barangays')->insertOrIgnore($chunk);
        }

        unset($data);
    }
}
