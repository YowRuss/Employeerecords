<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $columnsToDrop = [
                'spouse_first_name',
                'spouse_last_name',
                'spouse_occupation',
                'spouse_employer',
                'father_first_name',
                'father_last_name',
                'mother_maiden_first_name',
                'mother_maiden_last_name'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('pds_personal_info', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->string('spouse_first_name', 100)->nullable();
            $table->string('spouse_last_name', 100)->nullable();
            $table->string('spouse_occupation', 100)->nullable();
            $table->string('spouse_employer', 150)->nullable();
            $table->string('father_first_name', 100)->nullable();
            $table->string('father_last_name', 100)->nullable();
            $table->string('mother_maiden_first_name', 100)->nullable();
            $table->string('mother_maiden_last_name', 100)->nullable();
        });
    }
};
