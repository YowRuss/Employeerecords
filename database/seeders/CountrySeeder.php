<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Add this!

class CountrySeeder extends Seeder
{
    public function run()
    {
        // Open the CSV file from the seeders directory
        $csvFile = fopen(database_path('seeders/data.csv'), 'r');

        $firstline = true;

        // Loop through each row in the CSV
        while (($data = fgetcsv($csvFile, 2000, ',')) !== false) {
            // Skip the first row (the header: "Name,Code")
            if ($firstline) {
                $firstline = false;

                continue;
            }

            // Insert directly into the database table
            DB::table('countries')->insert([
                'name' => $data[0],
                'code' => $data[1],
            ]);
        }

        fclose($csvFile);
    }
}
