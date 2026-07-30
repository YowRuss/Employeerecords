<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teaching = [
            'Teacher I', 'Teacher II', 'Teacher III', 'Teacher IV', 'Teacher V', 'Teacher VI',
            'Master Teacher I', 'Master Teacher II', 'Principal', 'Head Teacher III', 'Head Teacher VI',
        ];

        $nonTeaching = [
            'Administrative Officer IV', 'Accountant', 'Admin Asst II', 'Admin Officer',
            'Administrative Aide III', 'Administrative Aide IV', 'Administrative Aide V', 'Administrative Aide VI',
            'Guidance Councilor I', 'Guidance Councilor II', 'Guidance Councilor III', 'Guidance Councilor IV',
            'Guidance Councilor V', 'Guidance Councilor VI', 'School Librarian',
        ];

        foreach ($teaching as $position) {
            Position::updateOrCreate(
                ['position_name' => $position],
                ['category' => 'Teaching']
            );
        }

        foreach ($nonTeaching as $position) {
            Position::updateOrCreate(
                ['position_name' => $position],
                ['category' => 'Non-Teaching']
            );
        }
    }
}
