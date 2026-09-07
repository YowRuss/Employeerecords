<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE leave_credit_settings MODIFY COLUMN employee_type ENUM('TEACHING', 'NON_TEACHING', 'GLOBAL')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to original. Note: will fail if there are 'GLOBAL' records still present.
        DB::statement("ALTER TABLE leave_credit_settings MODIFY COLUMN employee_type ENUM('TEACHING', 'NON_TEACHING')");
    }
};
