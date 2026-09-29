<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;

uses(DatabaseTransactions::class);

function paginationHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

test('employee lists page five people at a time', function () {
    $session = paginationHrSession();

    $attendance = $this->withSession($session)
        ->get(route('payroll.attendance.index', ['month' => 9, 'year' => 2026]));

    $attendance->assertSuccessful();
    expect($attendance->viewData('employees'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($attendance->viewData('employees')->perPage())->toBe(5);

    $profiles = $this->withSession($session)->get(route('hr.payroll.employees'));
    $profiles->assertSuccessful();
    expect($profiles->viewData('employees'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($profiles->viewData('employees')->perPage())->toBe(5)
        ->and($profiles->viewData('counts')['total'])->toBeGreaterThanOrEqual($profiles->viewData('employees')->total());

    $bir = $this->withSession($session)->get(route('hr.bir2316.index'));
    $bir->assertSuccessful();
    expect($bir->viewData('employees'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($bir->viewData('employees')->perPage())->toBe(5);

    $loans = $this->withSession($session)->get(route('payroll.loans.index'));
    $loans->assertSuccessful();
    expect($loans->viewData('loans'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($loans->viewData('loans')->perPage())->toBe(5);

    $leaves = $this->withSession($session)->get(route('hr.leave.index'));
    $leaves->assertSuccessful();
    expect($leaves->viewData('teachingEmployees'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($leaves->viewData('nonTeachingEmployees'))->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($leaves->viewData('unassignedEmployees'))->toBeInstanceOf(LengthAwarePaginator::class);
});
