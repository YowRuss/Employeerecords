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
        // Convert existing string data to tinyint values safely
        DB::statement("UPDATE pds_personal_info SET citizenship = '0' WHERE citizenship = 'Filipino' OR citizenship IS NULL");
        DB::statement("UPDATE pds_personal_info SET citizenship = '1' WHERE citizenship = 'Dual Citizenship'");
        // Clean any unexpected data
        DB::statement("UPDATE pds_personal_info SET citizenship = '0' WHERE citizenship NOT IN ('0', '1')");

        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->tinyInteger('citizenship')->default(0)->comment('0=Filipino, 1=Dual Citizenship')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->string('citizenship', 255)->nullable()->change();
        });

        DB::statement("UPDATE pds_personal_info SET citizenship = 'Filipino' WHERE citizenship = '0'");
        DB::statement("UPDATE pds_personal_info SET citizenship = 'Dual Citizenship' WHERE citizenship = '1'");
    }
};
