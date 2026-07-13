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
        Schema::create('pds_personal_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Section A: Personal Info
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('name_extension', 20)->nullable(); // Jr., Sr., III
            $table->date('date_of_birth');
            $table->string('place_of_birth', 255);
            $table->string('sex', 20);
            $table->string('civil_status', 50);
            $table->string('height', 20)->nullable();
            $table->string('weight', 20)->nullable();
            $table->string('blood_type', 10)->nullable();

            // Government IDs
            $table->string('gsis_no', 50)->nullable();
            $table->string('pagibig_no', 50)->nullable();
            $table->string('philhealth_no', 50)->nullable();
            $table->string('sss_no', 50)->nullable();
            $table->string('tin_no', 50)->nullable();
            $table->string('agency_employee_no', 50)->nullable();

            // Contact & Address
            $table->text('residential_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('telephone_no', 50)->nullable();
            $table->string('mobile_no', 50)->nullable();
            $table->string('email_address', 100)->nullable();

            // Section B: Family Background (Spouse & Parents)
            $table->string('spouse_first_name', 100)->nullable();
            $table->string('spouse_last_name', 100)->nullable();
            $table->string('spouse_occupation', 100)->nullable();
            $table->string('spouse_employer', 150)->nullable();
            $table->string('father_first_name', 100)->nullable();
            $table->string('father_last_name', 100)->nullable();
            $table->string('mother_maiden_first_name', 100)->nullable();
            $table->string('mother_maiden_last_name', 100)->nullable();

            $table->string('status', 30)->default('Draft'); // Draft, Pending, Approved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pds_personal_info');
    }
};
