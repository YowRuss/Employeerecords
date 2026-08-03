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
        // Convert existing data first to avoid cast issues
        DB::table('pds_personal_info')->where('sex', 'Male')->update(['sex' => '1']);
        DB::table('pds_personal_info')->where('sex', 'Female')->update(['sex' => '0']);
        DB::table('pds_personal_info')->where('sex', '')->orWhereNull('sex')->update(['sex' => '0']);

        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->tinyInteger('sex')->default(0)->comment('0=Female, 1=Male')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->string('sex', 20)->change();
        });

        // Convert back
        DB::table('pds_personal_info')->where('sex', '1')->update(['sex' => 'Male']);
        DB::table('pds_personal_info')->where('sex', '0')->update(['sex' => 'Female']);
    }
};
