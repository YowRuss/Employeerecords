<?php

use App\Models\LateDeduction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function employeeAttendanceSession(?User $employee = null): array
{
    $employee ??= User::where('role_id', 1)->first() ?? User::first();

    return [
        'user_id' => $employee ? $employee->id : 1,
        'role_id' => 1,
        'full_name' => $employee ? trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) : 'Test Employee',
    ];
}

test('my attendance shows a period list and calendar for the signed-in employee', function () {
    $employee = User::where('role_id', 1)->firstOrFail();

    LateDeduction::updateOrCreate(
        ['user_id' => $employee->id, 'payroll_period' => '2026-09'],
        [
            'minutes_late' => 23,
            'unexcused_absences' => 1.5,
            'dates_absent' => ['2026-09-01' => 1, '2026-09-02' => 0.5],
            'dates_late' => ['2026-09-14' => 15, '2026-09-15' => 8],
            'computed_amount' => 10,
            'absence_deduction_amount' => 20,
        ]
    );

    $html = $this->withSession(employeeAttendanceSession($employee))
        ->get(route('employee.attendance.index'))
        ->assertSuccessful()
        ->assertSee('My Attendance')
        ->assertSee('September 2026')
        ->assertSee('Payroll Periods')
        ->assertSee('Attendance Calendar')
        ->assertSee('Full Day Absent')
        ->assertSee('Half Day Absent')
        ->assertSee('Minutes Late')
        ->getContent();

    expect($html)
        ->toContain('id="period-list"')
        ->toContain('id="calendar-view"')
        ->toContain('class="list-group list-group-flush"')
        ->toContain('data-month="9"')
        ->toContain('data-year="2026"')
        ->toContain('2026-09-01')
        ->toContain('2026-09-14')
        ->toContain('class="collapse show" id="empPayrollSubmenu"');
});

test('my attendance does not list another employees deductions', function () {
    $employee = User::where('role_id', 1)->firstOrFail();
    $other = User::where('role_id', 1)->where('id', '!=', $employee->id)->first();

    if (! $other) {
        $this->markTestSkipped('A second employee is required to assert isolation.');
    }

    LateDeduction::updateOrCreate(
        ['user_id' => $other->id, 'payroll_period' => '2026-11'],
        [
            'minutes_late' => 40,
            'unexcused_absences' => 2,
            'dates_absent' => ['2026-11-03' => 1],
            'dates_late' => ['2026-11-04' => 40],
            'computed_amount' => 5,
            'absence_deduction_amount' => 5,
        ]
    );

    $this->withSession(employeeAttendanceSession($employee))
        ->get(route('employee.attendance.index'))
        ->assertSuccessful()
        ->assertDontSee('November 2026')
        ->assertDontSee('2026-11-03');
});
