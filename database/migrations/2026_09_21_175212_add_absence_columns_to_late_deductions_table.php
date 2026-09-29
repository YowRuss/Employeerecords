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
            $table->decimal('unexcused_absences', 5, 1)->default(0)->after('minutes_late');
            $table->decimal('absence_deduction_amount', 12, 2)->default(0.00)->after('unexcused_absences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('late_deductions', function (Blueprint $table) {
            $table->dropColumn(['unexcused_absences', 'absence_deduction_amount']);
        });
    }
};
