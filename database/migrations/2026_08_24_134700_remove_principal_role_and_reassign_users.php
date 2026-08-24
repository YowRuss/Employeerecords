<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reassign all Principal users (role_id 4) to HR Admin (role_id 2)
        DB::table('users')->where('role_id', 4)->update(['role_id' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse this without keeping track of who was a principal originally.
    }
};
