<?php

use App\Models\Loan;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function hrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

/**
 * Pick an employee who carries no loans yet, so assertions on totals are not
 * skewed by records already living in the development database.
 */
function anEmployee(): User
{
    return User::where('role_id', 1)->whereDoesntHave('loans')->first()
        ?? User::where('role_id', 1)->firstOrFail();
}

test('loan management page loads with the expected view data', function () {
    $response = $this->withSession(hrSession())->get(route('payroll.loans.index'));

    $response->assertStatus(200);
    $response->assertViewIs('payroll.loans.index');
    $response->assertViewHasAll(['loans', 'employees', 'loanTypes', 'totalOutstanding']);
    $response->assertSee('Loan Management');
    $response->assertSee('Currently deducting');
    $response->assertSee('All Loans');
    $response->assertSee(route('payroll.loans.store'));
});

test('loan management filters the table by agency, status, and employee name', function () {
    $employee = anEmployee();

    Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'GSIS Conso-Loan',
        'principal_amount' => 24000,
        'monthly_amortization' => 1000,
        'running_balance' => 24000,
    ]);

    Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'Pag-IBIG Multi-Purpose Loan',
        'principal_amount' => 12000,
        'monthly_amortization' => 500,
        'running_balance' => 0,
        'status' => 'Paid',
    ]);

    $gsis = $this->withSession(hrSession())->get(route('payroll.loans.index', [
        'type' => 'GSIS',
        'status' => 'Active',
    ]));

    $gsis->assertStatus(200);
    $gsis->assertSee('GSIS Conso-Loan');
    $gsis->assertSee('Manage Loan');

    $filteredTypes = $gsis->viewData('loans')->pluck('loan_type');
    expect($filteredTypes->every(fn ($type) => str_starts_with($type, 'GSIS')))->toBeTrue()
        ->and($filteredTypes)->toContain('GSIS Conso-Loan');

    $search = $this->withSession(hrSession())->get(route('payroll.loans.index', [
        'search' => $employee->last_name,
    ]));

    $search->assertStatus(200);
    $search->assertSee($employee->last_name);
});

test('storing a loan seeds the running balance with the full principal', function () {
    $employee = anEmployee();

    $this->withSession(hrSession())->post(route('payroll.loans.store'), [
        'user_id' => $employee->id,
        'loan_type' => 'GSIS',
        'principal_amount' => 60000,
        'monthly_amortization' => 5000,
    ])->assertRedirect(route('payroll.loans.index'));

    $loan = Loan::where('user_id', $employee->id)->latest('id')->first();

    expect($loan)->not->toBeNull()
        ->and((float) $loan->running_balance)->toBe(60000.00)
        ->and($loan->status)->toBe('Active');
});

test('a loan is rejected when the amortization exceeds the principal', function () {
    $employee = anEmployee();

    $this->withSession(hrSession())->post(route('payroll.loans.store'), [
        'user_id' => $employee->id,
        'loan_type' => 'Pag-IBIG',
        'principal_amount' => 1000,
        'monthly_amortization' => 5000,
    ])->assertSessionHasErrors('monthly_amortization');
});

test('active loan deductions accessor sums only outstanding active loans', function () {
    $employee = anEmployee();

    Loan::create(['user_id' => $employee->id, 'loan_type' => 'GSIS', 'principal_amount' => 10000, 'monthly_amortization' => 1500, 'running_balance' => 10000]);
    Loan::create(['user_id' => $employee->id, 'loan_type' => 'Pag-IBIG', 'principal_amount' => 5000, 'monthly_amortization' => 500, 'running_balance' => 5000]);
    Loan::create(['user_id' => $employee->id, 'loan_type' => 'Settled', 'principal_amount' => 3000, 'monthly_amortization' => 900, 'running_balance' => 0, 'status' => 'Paid']);

    expect($employee->fresh()->active_loan_deductions)->toBe(2000.00);
});

test('finalizing a payroll period amortizes loans and closes those fully paid', function () {
    $employee = anEmployee();

    $ongoing = Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'GSIS',
        'principal_amount' => 10000,
        'monthly_amortization' => 1500,
        'running_balance' => 10000,
    ]);

    $finalInstalment = Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'Pag-IBIG',
        'principal_amount' => 5000,
        'monthly_amortization' => 800,
        'running_balance' => 500,
    ]);

    $period = PayrollPeriod::create([
        'fund_cluster' => '01',
        'period_month' => 1,
        'period_year' => 2030,
        'status' => 'DRAFT',
    ]);

    PayrollRecord::create([
        'payroll_period_id' => $period->id,
        'user_id' => $employee->id,
        'basic_rate' => 30000,
        'gross_earned' => 30000,
        'loan_amortization' => 2300,
        'total_deductions' => 2300,
        'net_amount' => 27700,
    ]);

    $this->withSession(hrSession())
        ->post(route('hr.payroll.approve', $period->id))
        ->assertSessionHas('success');

    expect($period->fresh()->status)->toBe('FINALIZED')
        ->and((float) $ongoing->fresh()->running_balance)->toBe(8500.00)
        ->and($ongoing->fresh()->status)->toBe('Active')
        ->and((float) $finalInstalment->fresh()->running_balance)->toBe(0.00)
        ->and($finalInstalment->fresh()->status)->toBe('Paid');
});

test('an already finalized period cannot be amortized twice', function () {
    $employee = anEmployee();

    $loan = Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'GSIS',
        'principal_amount' => 10000,
        'monthly_amortization' => 1500,
        'running_balance' => 10000,
    ]);

    $period = PayrollPeriod::create([
        'fund_cluster' => '01',
        'period_month' => 2,
        'period_year' => 2030,
        'status' => 'FINALIZED',
    ]);

    PayrollRecord::create([
        'payroll_period_id' => $period->id,
        'user_id' => $employee->id,
        'basic_rate' => 30000,
        'gross_earned' => 30000,
        'loan_amortization' => 1500,
        'total_deductions' => 1500,
        'net_amount' => 28500,
    ]);

    $this->withSession(hrSession())
        ->post(route('hr.payroll.approve', $period->id))
        ->assertSessionHas('error');

    expect((float) $loan->fresh()->running_balance)->toBe(10000.00);
});
