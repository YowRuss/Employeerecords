<?php

use App\Models\PdsPersonalInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function leaveApplicant(): User
{
    return User::where('role_id', 1)->where('status', 'active')->firstOrFail();
}

function setApplicantSex(User $employee, int|string|null $sex): void
{
    PdsPersonalInfo::updateOrCreate(
        ['user_id' => $employee->id],
        [
            'first_name' => $employee->first_name ?: 'Test',
            'last_name' => $employee->last_name ?: 'Employee',
            'date_of_birth' => '1990-01-01',
            'place_of_birth' => 'Cebu',
            'sex' => $sex,
            'civil_status' => 'Single',
        ]
    );

    $employee->unsetRelation('pdsPersonalInfo');
}

test('female employees see maternity leave and not paternity leave', function () {
    $employee = leaveApplicant();
    setApplicantSex($employee, 0);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('leave.index'))
        ->assertSuccessful()
        ->assertSee('<option value="Maternity Leave"', false)
        ->assertDontSee('<option value="Paternity Leave"', false)
        ->assertSee('Vacation Leave', false)
        ->assertSee('Sick Leave', false);
});

test('male employees see paternity leave and not maternity leave', function () {
    $employee = leaveApplicant();
    setApplicantSex($employee, 1);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('leave.index'))
        ->assertSuccessful()
        ->assertSee('<option value="Paternity Leave"', false)
        ->assertDontSee('<option value="Maternity Leave"', false)
        ->assertSee('Vacation Leave', false);
});

test('a missing sex hides maternity and paternity leave', function () {
    $employee = leaveApplicant();
    $employee->pdsPersonalInfo()?->delete();
    $employee->unsetRelation('pdsPersonalInfo');

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('leave.index'))
        ->assertSuccessful()
        ->assertDontSee('<option value="Maternity Leave"', false)
        ->assertDontSee('<option value="Paternity Leave"', false)
        ->assertSee('Vacation Leave', false)
        ->assertSee('Sick Leave', false);
});

test('a male employee cannot submit maternity leave', function () {
    $employee = leaveApplicant();
    setApplicantSex($employee, 1);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->post(route('leave.store'), [
        'date_of_filing' => '2026-09-27',
        'position' => 'Teacher',
        'salary' => '27000',
        'leave_type' => 'Maternity Leave',
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-06',
        'commutation' => 'Not Requested',
    ])->assertSessionHasErrors('leave_type');
});
