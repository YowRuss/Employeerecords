<?php

use App\Models\SalaryGrade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function salarySettingsSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

test('the salary matrix defaults to step 1', function () {
    $response = $this->withSession(salarySettingsSession())
        ->get(route('payroll.salary_settings'));

    $response->assertSuccessful();
    $response->assertViewHas('step', 1);

    $grades = $response->viewData('salaryGrades');
    expect($grades->every(fn (SalaryGrade $grade) => (int) $grade->step === 1))->toBeTrue();
    $response->assertSee('Filter by Step Increment:');
    $response->assertSee('Step 1');
    $response->assertSee('Step 8');
});

test('the salary matrix can be filtered to another step', function () {
    $stepTwo = SalaryGrade::where('step', 2)->orderBy('grade')->first();

    $response = $this->withSession(salarySettingsSession())
        ->get(route('payroll.salary_settings', ['step' => 2]));

    $response->assertSuccessful();
    $response->assertViewHas('step', 2);

    $grades = $response->viewData('salaryGrades');
    expect($grades->every(fn (SalaryGrade $grade) => (int) $grade->step === 2))->toBeTrue();

    if ($stepTwo) {
        $response->assertSee('SG '.$stepTwo->grade);
        $response->assertSee(number_format($stepTwo->amount, 2));
    }
});

test('an out of range salary step falls back to step 1', function () {
    $response = $this->withSession(salarySettingsSession())
        ->get(route('payroll.salary_settings', ['step' => 99]));

    $response->assertSuccessful();
    $response->assertViewHas('step', 1);
});
