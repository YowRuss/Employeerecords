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
        Schema::create('saln_real_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('kind')->nullable();
            $table->string('exact_location')->nullable();
            $table->decimal('assessed_value', 15, 2)->nullable();
            $table->decimal('fair_market_value', 15, 2)->nullable();
            $table->string('acquisition_year')->nullable();
            $table->string('acquisition_mode')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('saln_personal_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('year_acquired')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('saln_liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nature')->nullable();
            $table->string('name_of_creditors')->nullable();
            $table->decimal('outstanding_balance', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('saln_business_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('business_name')->nullable();
            $table->string('business_address')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->string('date_of_acquisition')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('saln_business_interests');
        Schema::dropIfExists('saln_liabilities');
        Schema::dropIfExists('saln_personal_properties');
        Schema::dropIfExists('saln_real_properties');
    }
};
