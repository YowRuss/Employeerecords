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
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('event_type_id')->nullable()->after('title');
        });

        // 2. Seed default event types
        $defaultTypes = [
            'Company Meeting',
            'Training Session',
            'Team Building',
            'Celebration',
            'Client Event',
        ];

        foreach ($defaultTypes as $type) {
            DB::table('event_types')->insertOrIgnore([
                'name' => $type,
                'badge_color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Map existing string types to the new event_type_id
        $events = DB::table('events')->get();
        foreach ($events as $event) {
            if ($event->type) {
                $eventType = DB::table('event_types')->where('name', $event->type)->first();
                if ($eventType) {
                    DB::table('events')
                        ->where('id', $event->id)
                        ->update(['event_type_id' => $eventType->id]);
                }
            }
        }

        // 4. Make event_type_id required and add foreign key
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('event_type_id')->nullable(false)->change();
            $table->foreign('event_type_id')->references('id')->on('event_types')->onDelete('cascade');

            // Drop the old type column
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('type')->nullable()->after('title');
        });

        // Try to map back (basic mapping)
        $events = DB::table('events')->get();
        foreach ($events as $event) {
            if ($event->event_type_id) {
                $eventType = DB::table('event_types')->where('id', $event->event_type_id)->first();
                if ($eventType) {
                    DB::table('events')
                        ->where('id', $event->id)
                        ->update(['type' => $eventType->name]);
                }
            }
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });
    }
};
