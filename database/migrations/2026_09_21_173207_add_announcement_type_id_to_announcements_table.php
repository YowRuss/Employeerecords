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
        // 1. Add the new column
        Schema::table('announcements', function (Blueprint $table) {
            $table->unsignedBigInteger('announcement_type_id')->nullable()->after('title');
        });

        // 2. Seed default announcement types
        $defaultTypes = [
            'Policy Update',
            'Emergency Alert',
            'Reminder',
            'Strategic Message',
            'General Information',
        ];

        foreach ($defaultTypes as $type) {
            DB::table('announcement_types')->insertOrIgnore([
                'name' => $type,
                'badge_color' => 'secondary',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Map existing string types to the new announcement_type_id
        $announcements = DB::table('announcements')->get();
        foreach ($announcements as $announcement) {
            if ($announcement->type) {
                $announcementType = DB::table('announcement_types')->where('name', $announcement->type)->first();
                if ($announcementType) {
                    DB::table('announcements')
                        ->where('id', $announcement->id)
                        ->update(['announcement_type_id' => $announcementType->id]);
                }
            }
        }

        // 4. Make announcement_type_id required and add foreign key
        Schema::table('announcements', function (Blueprint $table) {
            $table->unsignedBigInteger('announcement_type_id')->nullable(false)->change();
            $table->foreign('announcement_type_id')->references('id')->on('announcement_types')->onDelete('cascade');

            // Drop the old type column
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('type')->nullable()->after('title');
        });

        // Try to map back (basic mapping)
        $announcements = DB::table('announcements')->get();
        foreach ($announcements as $announcement) {
            if ($announcement->announcement_type_id) {
                $announcementType = DB::table('announcement_types')->where('id', $announcement->announcement_type_id)->first();
                if ($announcementType) {
                    DB::table('announcements')
                        ->where('id', $announcement->id)
                        ->update(['type' => $announcementType->name]);
                }
            }
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['announcement_type_id']);
            $table->dropColumn('announcement_type_id');
        });
    }
};
