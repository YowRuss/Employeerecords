<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sanitize existing text values to NULL
        try {
            DB::statement("UPDATE pds_personal_info SET res_region = NULL WHERE res_region NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET res_province = NULL WHERE res_province NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET res_city = NULL WHERE res_city NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET res_barangay = NULL WHERE res_barangay NOT REGEXP '^[0-9]+$'");

            DB::statement("UPDATE pds_personal_info SET perm_region = NULL WHERE perm_region NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET perm_province = NULL WHERE perm_province NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET perm_city = NULL WHERE perm_city NOT REGEXP '^[0-9]+$'");
            DB::statement("UPDATE pds_personal_info SET perm_barangay = NULL WHERE perm_barangay NOT REGEXP '^[0-9]+$'");
        } catch (Throwable $e) {
            // Ignore regex errors if any
        }

        // Drop existing foreign keys if present
        $foreignKeys = [
            'pds_personal_info_res_region_foreign',
            'pds_personal_info_res_province_foreign',
            'pds_personal_info_res_city_foreign',
            'pds_personal_info_res_barangay_foreign',
            'pds_personal_info_perm_region_foreign',
            'pds_personal_info_perm_province_foreign',
            'pds_personal_info_perm_city_foreign',
            'pds_personal_info_perm_barangay_foreign',
            'fk_pds_res_region_id',
            'fk_pds_res_province_id',
            'fk_pds_res_city_id',
            'fk_pds_res_barangay_id',
        ];

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE pds_personal_info DROP FOREIGN KEY {$fk}");
            } catch (Throwable $e) {
                // Ignore if constraint does not exist
            }
        }

        // Alter column types to match int type of ref_ tables id column
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_region INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_province INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_city INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_barangay INT NULL');

        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_region INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_province INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_city INT NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_barangay INT NULL');

        // Set non-matching foreign key values to NULL
        try {
            DB::statement('UPDATE pds_personal_info SET res_region = NULL WHERE res_region IS NOT NULL AND res_region NOT IN (SELECT id FROM ref_regions)');
            DB::statement('UPDATE pds_personal_info SET res_province = NULL WHERE res_province IS NOT NULL AND res_province NOT IN (SELECT id FROM ref_provinces)');
            DB::statement('UPDATE pds_personal_info SET res_city = NULL WHERE res_city IS NOT NULL AND res_city NOT IN (SELECT id FROM ref_cities)');
            DB::statement('UPDATE pds_personal_info SET res_barangay = NULL WHERE res_barangay IS NOT NULL AND res_barangay NOT IN (SELECT id FROM ref_barangays)');

            DB::statement('UPDATE pds_personal_info SET perm_region = NULL WHERE perm_region IS NOT NULL AND perm_region NOT IN (SELECT id FROM ref_regions)');
            DB::statement('UPDATE pds_personal_info SET perm_province = NULL WHERE perm_province IS NOT NULL AND perm_province NOT IN (SELECT id FROM ref_provinces)');
            DB::statement('UPDATE pds_personal_info SET perm_city = NULL WHERE perm_city IS NOT NULL AND perm_city NOT IN (SELECT id FROM ref_cities)');
            DB::statement('UPDATE pds_personal_info SET perm_barangay = NULL WHERE perm_barangay IS NOT NULL AND perm_barangay NOT IN (SELECT id FROM ref_barangays)');
        } catch (Throwable $e) {
            // Ignore update errors
        }

        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->foreign('res_region', 'fk_pds_res_region_id')->references('id')->on('ref_regions')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('res_province', 'fk_pds_res_province_id')->references('id')->on('ref_provinces')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('res_city', 'fk_pds_res_city_id')->references('id')->on('ref_cities')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('res_barangay', 'fk_pds_res_barangay_id')->references('id')->on('ref_barangays')->onDelete('set null')->onUpdate('cascade');

            $table->foreign('perm_region', 'fk_pds_perm_region_id')->references('id')->on('ref_regions')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('perm_province', 'fk_pds_perm_province_id')->references('id')->on('ref_provinces')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('perm_city', 'fk_pds_perm_city_id')->references('id')->on('ref_cities')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('perm_barangay', 'fk_pds_perm_barangay_id')->references('id')->on('ref_barangays')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            try {
                $table->dropForeign('fk_pds_res_region_id');
                $table->dropForeign('fk_pds_res_province_id');
                $table->dropForeign('fk_pds_res_city_id');
                $table->dropForeign('fk_pds_res_barangay_id');
                $table->dropForeign('fk_pds_perm_region_id');
                $table->dropForeign('fk_pds_perm_province_id');
                $table->dropForeign('fk_pds_perm_city_id');
                $table->dropForeign('fk_pds_perm_barangay_id');
            } catch (Throwable $e) {
                // Ignore if drop Foreign fails
            }
        });

        DB::statement('ALTER TABLE pds_personal_info MODIFY res_region VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_province VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_city VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY res_barangay VARCHAR(150) NULL');

        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_region VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_province VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_city VARCHAR(150) NULL');
        DB::statement('ALTER TABLE pds_personal_info MODIFY perm_barangay VARCHAR(150) NULL');
    }
};
