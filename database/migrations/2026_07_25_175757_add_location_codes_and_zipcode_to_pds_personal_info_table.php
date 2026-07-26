<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds location code columns (res_region, res_province, res_city, res_barangay)
     * and res_zipcode to the pds_personal_info table.
     *
     * Foreign key / reference table relationships:
     * - res_region   => ref_regions (region_code)
     * - res_province => ref_provinces (province_code)
     * - res_city     => ref_cities (city_code)
     * - res_barangay => ref_barangays (brgy_code)
     */
    public function up(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            // Add res_zipcode column if it does not exist
            if (! Schema::hasColumn('pds_personal_info', 'res_zipcode')) {
                $table->string('res_zipcode', 20)->nullable()->after('res_zip');
            }

            // Ensure location code columns exist to store codes from ref_ tables
            if (! Schema::hasColumn('pds_personal_info', 'res_region')) {
                $table->string('res_region', 50)->nullable()->comment('References ref_regions(region_code)');
            }

            if (! Schema::hasColumn('pds_personal_info', 'res_province')) {
                $table->string('res_province', 50)->nullable()->comment('References ref_provinces(province_code)');
            }

            if (! Schema::hasColumn('pds_personal_info', 'res_city')) {
                $table->string('res_city', 50)->nullable()->comment('References ref_cities(city_code)');
            }

            if (! Schema::hasColumn('pds_personal_info', 'res_barangay')) {
                $table->string('res_barangay', 50)->nullable()->comment('References ref_barangays(brgy_code)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            if (Schema::hasColumn('pds_personal_info', 'res_zipcode')) {
                $table->dropColumn('res_zipcode');
            }
        });
    }
};
