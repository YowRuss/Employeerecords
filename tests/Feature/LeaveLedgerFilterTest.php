<?php

use App\Enums\PositionCategory;
use App\Models\LeaveApplication;
use App\Models\PdsPersonalInfo;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

function ledgerHrSession(): array
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

function ledgerLeave(User $employee, string $marker): LeaveApplication
{
    return LeaveApplication::create([
        'user_id' => $employee->id,
        'office_department' => 'CNHS-JH',
        'date_of_filing' => '2026-09-27',
        'position' => 'STAFF',
        'salary' => '27000',
        'leave_type' => 'Vacation Leave',
        'working_days' => 1,
        'inclusive_dates' => $marker,
        'commutation' => 'Not Requested',
        'status' => 'PENDING',
        'service_credits_used' => 0,
        'seminar_credits_used' => 0,
    ]);
}

test('leave ledger filters by teaching category and sex while keeping status', function () {
    $hr = ledgerHrSession();
    $employees = User::where('role_id', 1)->where('status', 'active')->orderBy('id')->take(2)->get();
    expect($employees)->toHaveCount(2);

    $maleTeacher = $employees[0];
    $femaleClerk = $employees[1];

    $teacherPosition = Position::create([
        'position_name' => 'Ledger Filter Teacher',
        'category' => PositionCategory::Teaching,
    ]);
    $clerkPosition = Position::create([
        'position_name' => 'Ledger Filter Clerk',
        'category' => PositionCategory::NonTeaching,
    ]);

    DB::table('users')->where('id', $maleTeacher->id)->update([
        'position_id' => $teacherPosition->id,
        'employee_type' => 1,
    ]);
    DB::table('users')->where('id', $femaleClerk->id)->update([
        'position_id' => $clerkPosition->id,
        'employee_type' => 0,
    ]);

    foreach ([$maleTeacher, $femaleClerk] as $index => $employee) {
        PdsPersonalInfo::updateOrCreate(
            ['user_id' => $employee->id],
            [
                'first_name' => $employee->first_name ?: 'Test',
                'last_name' => $employee->last_name ?: 'Employee',
                'date_of_birth' => '1990-01-01',
                'place_of_birth' => 'Cebu',
                'sex' => $index === 0 ? 1 : 0,
                'civil_status' => 'Single',
            ]
        );
    }

    $teacherMarker = 'LEDGER-TEACH-MALE-9X7';
    $clerkMarker = 'LEDGER-CLERK-FEMALE-4Q2';
    ledgerLeave($maleTeacher, $teacherMarker);
    ledgerLeave($femaleClerk, $clerkMarker);

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->get(route('hr.leave.index', ['category' => 'teaching', 'sex' => 'male', 'status' => 'PENDING']))
        ->assertSuccessful()
        ->assertSee($teacherMarker, false)
        ->assertDontSee($clerkMarker, false)
        ->assertSee('Teaching Positions', false)
        ->assertSee('Non-Teaching Positions', false)
        ->assertSee('name="category" value="teaching"', false)
        ->assertSee('name="sex" value="male"', false)
        ->assertSee('value="PENDING" selected', false);

    $this->actingAs($hr['user'])->withSession($hr['session'])
        ->get(route('hr.leave.index', ['category' => 'non-teaching', 'sex' => 'female']))
        ->assertSuccessful()
        ->assertSee($clerkMarker, false)
        ->assertDontSee($teacherMarker, false);
});
