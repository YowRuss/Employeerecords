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
            $table->dropColumn([
                'spouse_middle_name',
                'spouse_name_extension',
                'spouse_business_address',
                'spouse_telephone',
                'father_middle_name',
                'father_name_extension',
                'mother_maiden_middle_name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->string('spouse_middle_name', 100)->nullable();
            $table->string('spouse_name_extension', 50)->nullable();
            $table->string('spouse_business_address', 255)->nullable();
            $table->string('spouse_telephone', 50)->nullable();
            $table->string('father_middle_name', 100)->nullable();
            $table->string('father_name_extension', 50)->nullable();
            $table->string('mother_maiden_middle_name', 100)->nullable();
        });
    }
};
