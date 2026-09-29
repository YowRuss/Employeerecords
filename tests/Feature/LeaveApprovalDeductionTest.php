<?php

use App\Models\LeaveApplication;
use App\Models\LeaveCreditBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function approvalHrSession(): array
{
    $hr = User::whereIn('role_id', [2, 3])->firstOrFail();

    return [
        'user' => $hr,
        'session' => [
            'user_id' => $hr->id,
            'role_id' => (int) $hr->role_id,
        ],
    ];
}

function approvalEmployee(): User
{
    return User::where('role_id', 1)->where('status', 'active')->firstOrFail();
}

function approvalLeave(User $employee, string $type, float $days = 2): LeaveApplication
{
    return LeaveApplication::create([
        'user_id' => $employee->id,
        'office_department' => 'CNHS-JH',
        'date_of_filing' => '2026-09-27',
        'position' => 'TEACHER',
        'salary' => '27000',
        'leave_type' => $type,
        'leave_type_others' => $type === 'Others' ? 'SPECIAL SKILL LEAVE' : null,
        'working_days' => $days,
        'inclusive_dates' => 'SEP 28, 2026 - SEP 29, 2026',
        'commutation' => 'Not Requested',
        'status' => 'PENDING',
        'service_credits_used' => 0,
        'seminar_credits_used' => 0,
    ]);
}

test('approving others does not deduct vacation or sick leave', function () {
    $hr = approvalHrSession();
    $employee = approvalEmployee();
    LeaveCreditBalance::updateOrCreate(
        ['user_id' => $employee->id],
        ['vl_balance' => 10, 'sl_balance' => 8, 'service_credits' => 0, 'seminar_credits' => 0]
    );
    $leave = approvalLeave($employee, 'Others');

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->get(route('hr.leave.index'))
        ->assertSuccessful()
        ->assertSee('Deduct from Vacation Leave', false)
        ->assertSee('Deduct from Sick Leave', false)
        ->assertSee('For special statutory leaves or "Others", leave these at 0 unless a specific deduction is required.', false)
        ->assertSee('data-type="Others"', false);

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->post(route('hr.leave.update_status', $leave->id), [
            'status' => 'APPROVED',
            'vl_deduct' => 0,
            'sl_deduct' => 0,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $balance = LeaveCreditBalance::where('user_id', $employee->id)->first();
    expect((float) $balance->vl_balance)->toBe(10.0)
        ->and((float) $balance->sl_balance)->toBe(8.0)
        ->and($leave->fresh()->status)->toBe('APPROVED')
        ->and((float) $leave->fresh()->credits_deducted)->toBe(0.0)
        ->and((float) $leave->fresh()->days_without_pay)->toBe(0.0);
});

test('approving vacation leave deducts the amount hr entered', function () {
    $hr = approvalHrSession();
    $employee = approvalEmployee();
    LeaveCreditBalance::updateOrCreate(
        ['user_id' => $employee->id],
        ['vl_balance' => 10, 'sl_balance' => 8, 'service_credits' => 0, 'seminar_credits' => 0]
    );
    $leave = approvalLeave($employee, 'Vacation Leave', 3);

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->post(route('hr.leave.update_status', $leave->id), [
            'status' => 'APPROVED',
            'vl_deduct' => 1.5,
            'sl_deduct' => 0,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $balance = LeaveCreditBalance::where('user_id', $employee->id)->first();
    expect((float) $balance->vl_balance)->toBe(8.5)
        ->and((float) $balance->sl_balance)->toBe(8.0)
        ->and((float) $leave->fresh()->days_with_pay)->toBe(1.5)
        ->and((float) $leave->fresh()->days_without_pay)->toBe(1.5);
});

test('approving sick leave deducts only the sick leave amount', function () {
    $hr = approvalHrSession();
    $employee = approvalEmployee();
    LeaveCreditBalance::updateOrCreate(
        ['user_id' => $employee->id],
        ['vl_balance' => 10, 'sl_balance' => 8, 'service_credits' => 0, 'seminar_credits' => 0]
    );
    $leave = approvalLeave($employee, 'Sick Leave', 2);

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->post(route('hr.leave.update_status', $leave->id), [
            'status' => 'APPROVED',
            'vl_deduct' => 0,
            'sl_deduct' => 2,
        ])
        ->assertSessionHas('success');

    $balance = LeaveCreditBalance::where('user_id', $employee->id)->first();
    expect((float) $balance->sl_balance)->toBe(6.0)
        ->and((float) $balance->vl_balance)->toBe(10.0);
});

test('others still requires a specified reason', function () {
    $employee = approvalEmployee();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('leave.index'))
        ->post(route('leave.store'), [
            'date_of_filing' => '2026-09-27',
            'position' => 'Teacher',
            'salary' => '27000',
            'leave_type' => 'Others',
            'leave_type_others' => '',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-06',
            'commutation' => 'Not Requested',
        ])
        ->assertSessionHasErrors('leave_type_others');
});
