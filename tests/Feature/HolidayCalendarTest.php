<?php

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function holidayHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

test('the holiday calendar page lists dates newest first and keeps payroll open', function () {
    Holiday::create([
        'title' => 'TEST-OLDER-HOLIDAY',
        'holiday_date' => '2026-01-01',
        'type' => 'Regular',
    ]);

    Holiday::create([
        'title' => 'TEST-NEWER-HOLIDAY',
        'holiday_date' => '2026-12-25',
        'type' => 'Special Non-Working',
    ]);

    $html = $this->withSession(holidayHrSession())
        ->get(route('payroll.holidays.index'))
        ->assertSuccessful()
        ->assertViewIs('payroll.holidays.index')
        ->assertSee('Holiday Calendar')
        ->assertSee('TEST-NEWER-HOLIDAY')
        ->assertSee('TEST-OLDER-HOLIDAY')
        ->assertSee('Add New Holiday')
        ->getContent();

    expect($html)->toContain('class="collapse show" id="payrollSubmenu"')
        ->and($html)->toContain('id="calendar"')
        ->and($html)->toContain('id="holidayDate"')
        ->and($html)->not->toContain('id="holidaysTable"');
    expect(strpos($html, 'TEST-NEWER-HOLIDAY'))->toBeLessThan(strpos($html, 'TEST-OLDER-HOLIDAY'));
});

test('the holiday calendar api returns dated events colored by type', function () {
    $regular = Holiday::create([
        'title' => 'TEST-API-REGULAR',
        'holiday_date' => '2026-06-12',
        'type' => 'Regular',
    ]);

    $special = Holiday::create([
        'title' => 'TEST-API-SPECIAL',
        'holiday_date' => '2026-08-21',
        'type' => 'Special Non-Working',
    ]);

    $suspension = Holiday::create([
        'title' => 'TEST-API-SUSPENSION',
        'holiday_date' => '2026-09-15',
        'type' => 'Suspension',
    ]);

    $this->withSession(holidayHrSession())
        ->getJson(route('payroll.holidays.events'))
        ->assertSuccessful()
        ->assertJsonFragment([
            'id' => $regular->id,
            'title' => 'TEST-API-REGULAR',
            'start' => '2026-06-12',
            'color' => '#dc3545',
            'allDay' => true,
        ])
        ->assertJsonFragment([
            'id' => $special->id,
            'title' => 'TEST-API-SPECIAL',
            'start' => '2026-08-21',
            'color' => '#fd7e14',
            'allDay' => true,
        ])
        ->assertJsonFragment([
            'id' => $suspension->id,
            'title' => 'TEST-API-SUSPENSION',
            'start' => '2026-09-15',
            'color' => '#6c757d',
            'allDay' => true,
        ]);
});

test('hr can declare update and remove a holiday', function () {
    $session = holidayHrSession();

    $this->withSession($session)
        ->post(route('payroll.holidays.store'), [
            'title' => 'TEST-INDEPENDENCE-DAY',
            'holiday_date' => '2026-06-12',
            'type' => 'Regular',
        ])
        ->assertRedirect(route('payroll.holidays.index'));

    $holiday = Holiday::where('title', 'TEST-INDEPENDENCE-DAY')->first();

    expect($holiday)->not->toBeNull();

    $this->withSession($session)
        ->put(route('payroll.holidays.update', $holiday), [
            'title' => 'TEST-TYPHOON-SUSPENSION',
            'holiday_date' => '2026-09-15',
            'type' => 'Suspension',
        ])
        ->assertRedirect(route('payroll.holidays.index'));

    $holiday->refresh();

    expect($holiday->title)->toBe('TEST-TYPHOON-SUSPENSION')
        ->and($holiday->type->value)->toBe('Suspension');

    $this->withSession($session)
        ->delete(route('payroll.holidays.destroy', $holiday))
        ->assertRedirect(route('payroll.holidays.index'));

    expect(Holiday::find($holiday->id))->toBeNull();
});

test('holiday store rejects an unknown type', function () {
    $this->withSession(holidayHrSession())
        ->from(route('payroll.holidays.index'))
        ->post(route('payroll.holidays.store'), [
            'title' => 'TEST-INVALID-TYPE',
            'holiday_date' => '2026-06-12',
            'type' => 'Working Day',
        ])
        ->assertRedirect(route('payroll.holidays.index'))
        ->assertSessionHasErrors('type');

    expect(Holiday::where('title', 'TEST-INVALID-TYPE')->exists())->toBeFalse();
});

test('attendance page shows a holiday notice for the selected month', function () {
    Holiday::create([
        'title' => 'TEST-NATIONAL-HEROES-DAY',
        'holiday_date' => '2026-08-31',
        'type' => 'Regular',
    ]);

    Holiday::create([
        'title' => 'TEST-OTHER-MONTH-HOLIDAY',
        'holiday_date' => '2026-06-12',
        'type' => 'Regular',
    ]);

    $this->withSession(holidayHrSession())
        ->get(route('payroll.attendance.index', ['month' => 8, 'year' => 2026]))
        ->assertSuccessful()
        ->assertSee('Holiday Notice')
        ->assertSee('TEST-NATIONAL-HEROES-DAY')
        ->assertSee('Do not mark employees absent')
        ->assertDontSee('TEST-OTHER-MONTH-HOLIDAY');
});

test('attendance page hides the holiday notice when the month is clear', function () {
    $this->withSession(holidayHrSession())
        ->get(route('payroll.attendance.index', ['month' => 3, 'year' => 2099]))
        ->assertSuccessful()
        ->assertDontSee('Holiday Notice');
});
