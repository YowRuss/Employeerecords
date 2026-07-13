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
        Schema::create('pds_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('level', 50); // Elementary, Secondary, Vocational, College, Graduate
            $table->string('school_name', 200);
            $table->string('degree_course', 200)->nullable();
            $table->string('period_from', 4)->nullable(); // Year
            $table->string('period_to', 4)->nullable();   // Year
            $table->string('highest_level_earned', 100)->nullable();
            $table->string('year_graduated', 4)->nullable();
            $table->string('scholarship_honors', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pds_education');
    }
};
