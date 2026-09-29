<?php

use App\Models\Holiday;
use App\Models\LateDeduction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function attendanceHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

function anActiveEmployee(): User
{
    return User::where('role_id', 1)->where('status', 'active')->firstOrFail();
}

test('the attendance page uses a read-only absence field and calendar modal', function () {
    Holiday::create([
        'title' => 'TEST-CALENDAR-HOLIDAY',
        'holiday_date' => '2026-09-04',
        'type' => 'Regular',
    ]);

    $employee = User::where('role_id', 1)
        ->where('status', 'active')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->firstOrFail();

    $html = $this->withSession(attendanceHrSession())
        ->get(route('payroll.attendance.index', ['month' => 9, 'year' => 2026]))
        ->assertSuccessful()
        ->assertSee('aria-label="Mark absent days"', false)
        ->assertSee('attendanceCalendarModal', false)
        ->assertSee('TEST-CALENDAR-HOLIDAY')
        ->assertSee('Save to Employee')
        ->assertSee('Total Unexcused Absences:')
        ->assertSee('aria-label="Mark minutes late"', false)
        ->assertSee('latesCalendarModal', false)
        ->assertSee('Total Minutes Late:')
        ->assertSee('lateMinutesModal', false)
        ->assertSee('Minutes Late')
        ->getContent();

    $employeeId = $employee->id;

    expect($html)
        ->toContain('class="btn btn-sm btn-accent fw-bold shadow-sm text-nowrap mark-attendance-btn"')
        ->toContain('class="btn btn-sm btn-accent fw-bold shadow-sm text-nowrap mark-lates-btn"')
        ->toContain('id="attendanceCalendarModal"')
        ->toContain('id="latesCalendarModal"')
        ->toContain('id="lateMinutesModal"')
        ->toContain('name="attendance['.$employeeId.'][dates_absent]"')
        ->toContain('name="attendance['.$employeeId.'][dates_late]"')
        ->toContain('readonly');
});

test('saving attendance stores date weights and the summed day count', function () {
    $employee = anActiveEmployee();

    $this->withSession(attendanceHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 9,
            'period_year' => 2026,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 0,
                    'absent_days' => 2,
                    'dates_absent' => json_encode([
                        '2026-09-01' => 1,
                        '2026-09-02' => 1,
                    ]),
                ],
            ],
        ])
        ->assertRedirect(route('payroll.attendance.index', ['month' => 9, 'year' => 2026]));

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2026-09')
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->dates_absent)->toMatchArray(['2026-09-01' => 1, '2026-09-02' => 1])
        ->and((float) $record->unexcused_absences)->toBe(2.0);
});

test('saving a full day and a half day stores a 1.5 day absence total', function () {
    $employee = anActiveEmployee();

    $this->withSession(attendanceHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 9,
            'period_year' => 2026,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 0,
                    'absent_days' => 1.5,
                    'dates_absent' => json_encode([
                        '2026-09-01' => 1,
                        '2026-09-02' => 0.5,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2026-09')
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->dates_absent)->toMatchArray(['2026-09-01' => 1, '2026-09-02' => 0.5])
        ->and((float) $record->unexcused_absences)->toBe(1.5)
        ->and((float) $record->absence_deduction_amount)->toBe(round($employee->base_salary / 22 * 1.5, 2));
});

test('save lates drops weekends holidays and dates outside the selected month', function () {
    $employee = anActiveEmployee();

    Holiday::create([
        'title' => 'TEST-BLOCKED-HOLIDAY',
        'holiday_date' => '2026-09-04',
        'type' => 'Suspension',
    ]);

    $this->withSession(attendanceHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 9,
            'period_year' => 2026,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 0,
                    'absent_days' => 5,
                    'dates_absent' => json_encode([
                        '2026-09-01' => 1,
                        '2026-09-04' => 1,
                        '2026-09-05' => 0.5,
                        '2026-09-06' => 1,
                        '2026-08-31' => 1,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2026-09')
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->dates_absent)->toMatchArray(['2026-09-01' => 1])
        ->and((float) $record->unexcused_absences)->toBe(1.0);
});

test('saving lates stores the date-to-minutes map and the summed total', function () {
    $employee = anActiveEmployee();

    $this->withSession(attendanceHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 9,
            'period_year' => 2026,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 23,
                    'absent_days' => 0,
                    'dates_late' => json_encode([
                        '2026-09-14' => 15,
                        '2026-09-15' => 8,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2026-09')
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->dates_late)->toMatchArray(['2026-09-14' => 15, '2026-09-15' => 8])
        ->and((int) $record->minutes_late)->toBe(23)
        ->and((float) $record->computed_amount)->toBe(round($employee->base_salary / 22 / 8 / 60 * 23, 2));
});

test('save lates drops weekend holiday and out-of-month tardy dates', function () {
    $employee = anActiveEmployee();

    Holiday::create([
        'title' => 'TEST-LATE-BLOCKED-HOLIDAY',
        'holiday_date' => '2026-09-04',
        'type' => 'Regular',
    ]);

    $this->withSession(attendanceHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 9,
            'period_year' => 2026,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 40,
                    'absent_days' => 0,
                    'dates_late' => json_encode([
                        '2026-09-01' => 15,
                        '2026-09-04' => 10,
                        '2026-09-05' => 8,
                        '2026-08-31' => 7,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2026-09')
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->dates_late)->toMatchArray(['2026-09-01' => 15])
        ->and((int) $record->minutes_late)->toBe(15);
});
