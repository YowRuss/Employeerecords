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
        Schema::table('late_deductions', function (Blueprint $table) {
            $table->json('dates_absent')->nullable()->after('unexcused_absences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('late_deductions', function (Blueprint $table) {
            $table->dropColumn('dates_absent');
        });
    }
};
