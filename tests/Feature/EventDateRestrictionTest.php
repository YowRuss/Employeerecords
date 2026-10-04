<?php

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('the create event form blocks past dates and times and the server rejects them', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-28 04:00:00', 'UTC'));

    try {
        $today = now()->timezone('Asia/Manila')->toDateString();
        $hr = User::where('role_id', 2)->firstOrFail();
        $type = EventType::query()->first() ?? EventType::create([
            'name' => 'Training',
            'badge_color' => 'success',
        ]);

        $session = [
            'user_id' => $hr->id,
            'role_id' => 2,
        ];

        $this->actingAs($hr)->withSession($session)
            ->get(route('events.index'))
            ->assertSuccessful()
            ->assertSee('min="'.$today.'"', false)
            ->assertSee('id="event_time"', false);

        $this->actingAs($hr)->withSession($session)
            ->from(route('events.index'))
            ->post(route('events.store'), [
                'title' => 'PAST DATE EVENT BLOCK',
                'event_type_id' => $type->id,
                'event_date' => now()->timezone('Asia/Manila')->subDay()->toDateString(),
                'event_time' => '09:00',
                'venue' => 'Conference Room',
            ])
            ->assertRedirect(route('events.index'))
            ->assertSessionHasErrors([
                'event_date' => 'The event date must be today or a future date.',
            ]);

        expect(Event::where('title', 'PAST DATE EVENT BLOCK')->exists())->toBeFalse();

        $this->actingAs($hr)->withSession($session)
            ->from(route('events.index'))
            ->post(route('events.store'), [
                'title' => 'PAST TIME EVENT BLOCK',
                'event_type_id' => $type->id,
                'event_date' => $today,
                'event_time' => '11:00',
                'venue' => 'Conference Room',
            ])
            ->assertRedirect(route('events.index'))
            ->assertSessionHasErrors([
                'event_time' => 'The event time must be the current time or a later time.',
            ]);

        expect(Event::where('title', 'PAST TIME EVENT BLOCK')->exists())->toBeFalse();

        $this->actingAs($hr)->withSession($session)
            ->post(route('events.store'), [
                'title' => 'TODAY EVENT ALLOWED',
                'event_type_id' => $type->id,
                'event_date' => $today,
                'event_time' => '13:00',
                'venue' => 'Conference Room',
            ])
            ->assertSessionHas('success');

        expect(Event::where('title', 'TODAY EVENT ALLOWED')->exists())->toBeTrue();

        $this->actingAs($hr)->withSession($session)
            ->post(route('events.store'), [
                'title' => 'FUTURE MORNING EVENT ALLOWED',
                'event_type_id' => $type->id,
                'event_date' => now()->timezone('Asia/Manila')->addDay()->toDateString(),
                'event_time' => '08:00',
                'venue' => 'Conference Room',
            ])
            ->assertSessionHas('success');

        expect(Event::where('title', 'FUTURE MORNING EVENT ALLOWED')->exists())->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});

test('assigning an event adviser publishes an announcement with a type', function () {
    $hr = User::where('role_id', 2)->firstOrFail();
    $adviser = User::where('role_id', 1)->firstOrFail();
    $type = EventType::query()->first() ?? EventType::create([
        'name' => 'Training',
        'badge_color' => 'success',
    ]);
    $date = now()->timezone('Asia/Manila')->addDays(40)->toDateString();

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->post(route('events.store'), [
        'title' => 'ADVISER ANNOUNCEMENT TYPE',
        'event_type_id' => $type->id,
        'event_date' => $date,
        'event_time' => '09:00',
        'venue' => 'Conference Room',
        'adviser_id' => $adviser->id,
        'override_conflict' => 1,
    ])->assertSessionHas('success');

    $announcement = Announcement::with('announcementType')
        ->where('title', 'Official Adviser Assignment: ADVISER ANNOUNCEMENT TYPE')
        ->first();

    expect($announcement)->not->toBeNull()
        ->and($announcement->announcement_type_id)->not->toBeNull()
        ->and($announcement->announcementType->name)->toBe('Assignment');
});
