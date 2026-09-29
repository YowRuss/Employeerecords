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
        DB::statement("ALTER TABLE leave_credit_logs MODIFY leave_bucket ENUM('VL', 'SL', 'SERVICE_CREDIT', 'SEMINAR_CREDIT') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE leave_credit_logs MODIFY leave_bucket ENUM('VL', 'SL', 'SERVICE_CREDIT') NOT NULL");
    }
};
