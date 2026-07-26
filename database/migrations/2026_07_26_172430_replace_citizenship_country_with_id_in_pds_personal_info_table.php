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
            $table->dropColumn('citizenship_country');
            $table->foreignId('citizenship_country_id')->nullable()->after('citizenship')->constrained('countries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_personal_info', function (Blueprint $table) {
            $table->dropForeign(['citizenship_country_id']);
            $table->dropColumn('citizenship_country_id');
            $table->string('citizenship_country')->nullable()->after('citizenship');
        });
    }
};
