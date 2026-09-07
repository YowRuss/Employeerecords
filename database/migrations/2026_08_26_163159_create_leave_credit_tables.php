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
        Schema::create('leave_credit_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 6, 2)->default(0.00);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id', 'uq_user_balance');
        });

        Schema::create('leave_credit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('source', ['monthly_accrual', 'seminar', 'leave_deduction', 'manual_adjustment']);
            $table->decimal('amount', 6, 2)->comment('Positive = credit added, Negative = credit deducted');
            $table->decimal('balance_after', 6, 2);
            $table->string('reference_type', 50)->nullable()->comment('e.g. seminar, leave_application');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->decimal('hours', 5, 2);
            $table->decimal('rate_applied', 4, 2)->comment('Snapshot of rate used (1.00 or 1.50) at time of approval');
            $table->decimal('credits_earned', 6, 2);
            $table->date('date_attended');
            $table->string('certificate_path', 255)->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });

        Schema::create('leave_credit_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('employee_type', ['TEACHING', 'NON_TEACHING']);
            $table->string('setting_key', 100);
            $table->string('setting_value', 100);
            $table->string('description', 255)->nullable();

            $table->unique(['employee_type', 'setting_key'], 'uq_type_key');
        });

        DB::table('leave_credit_settings')->insert([
            [
                'employee_type' => 'NON_TEACHING',
                'setting_key' => 'monthly_accrual_rate',
                'setting_value' => '15',
                'description' => 'Leave credits earned per month worked',
            ],
            [
                'employee_type' => 'NON_TEACHING',
                'setting_key' => 'seminar_hour_to_credit_rate',
                'setting_value' => '1',
                'description' => 'Leave credits earned per seminar hour',
            ],
            [
                'employee_type' => 'TEACHING',
                'setting_key' => 'monthly_accrual_rate',
                'setting_value' => '0',
                'description' => 'Teaching staff do not accrue monthly credits (semestral break in lieu)',
            ],
            [
                'employee_type' => 'TEACHING',
                'setting_key' => 'seminar_hour_to_credit_rate',
                'setting_value' => '1.5',
                'description' => 'Leave credits earned per seminar hour (higher rate for teaching staff)',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credit_settings');
        Schema::dropIfExists('seminars');
        Schema::dropIfExists('leave_credit_logs');
        Schema::dropIfExists('leave_credit_balances');
    }
};
