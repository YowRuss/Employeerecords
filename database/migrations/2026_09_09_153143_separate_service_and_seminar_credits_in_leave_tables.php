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
        Schema::table('leave_credit_balances', function (Blueprint $table) {
            // Rename existing to seminar_credits
            $table->renameColumn('service_credits', 'seminar_credits');
        });

        Schema::table('leave_credit_balances', function (Blueprint $table) {
            // Add the new service_credits column
            $table->decimal('service_credits', 6, 2)->default(0.00)->after('sl_balance');
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->decimal('service_credits_used', 6, 2)->default(0.00)->after('pay_status');
            $table->decimal('seminar_credits_used', 6, 2)->default(0.00)->after('service_credits_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn(['service_credits_used', 'seminar_credits_used']);
        });

        Schema::table('leave_credit_balances', function (Blueprint $table) {
            $table->dropColumn('service_credits');
        });

        Schema::table('leave_credit_balances', function (Blueprint $table) {
            $table->renameColumn('seminar_credits', 'service_credits');
        });
    }
};
