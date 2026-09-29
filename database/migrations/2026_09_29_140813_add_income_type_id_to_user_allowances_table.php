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
        Schema::table('user_allowances', function (Blueprint $table) {
            $table->foreignId('income_type_id')->nullable()->constrained('income_types')->nullOnDelete()->after('user_id');
            $table->string('allowance_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_allowances', function (Blueprint $table) {
            $table->dropForeign(['income_type_id']);
            $table->dropColumn('income_type_id');
            $table->string('allowance_name')->nullable(false)->change();
        });
    }
};
