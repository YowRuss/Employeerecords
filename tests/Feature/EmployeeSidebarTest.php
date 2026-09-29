<?php

use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class)->group('employee-self-service');

function employeeSidebarSession(): array
{
    $employee = User::where('role_id', 1)->first() ?? User::first();

    return [
        'user_id' => $employee ? $employee->id : 1,
        'role_id' => 1,
        'full_name' => $employee ? trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) : 'Test Employee',
    ];
}

test('the employee sidebar groups personal info and payroll into collapsible menus', function () {
    $response = $this->withSession(employeeSidebarSession())->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Personal Info');
    $response->assertSee('Payroll');
    $response->assertSee('My PDS');
    $response->assertSee('My SALN');
    $response->assertSee('Service Record');
    $response->assertSee('My Payslips');
    $response->assertSee('My Loans');
    $response->assertSee('My Attendance');
    $response->assertSee('Leave Requests');
    $response->assertSee('Tax Documents');
    $response->assertSee('Announcements');
    $response->assertSee('Events');
    $response->assertSee('Message HR');
    $response->assertSee('data-bs-toggle="collapse"', false);
    $response->assertSee('data-bs-target="#empPersonalInfoSubmenu"', false);
    $response->assertSee('data-bs-target="#empPayrollSubmenu"', false);
    $response->assertSee('submenu-arrow', false);
});

test('viewing SALN keeps the personal info dropdown open and highlights the child link', function () {
    $html = $this->withSession(employeeSidebarSession())
        ->get(route('saln.index'))
        ->assertStatus(200)
        ->getContent();

    expect($html)
        ->toContain('id="empPersonalInfoSubmenu"')
        ->toContain('collapse show')
        ->toContain(route('saln.index'));

    expect($html)->toContain('class="collapse show" id="empPersonalInfoSubmenu"')
        ->and($html)->not->toContain('class="collapse show" id="empPayrollSubmenu"');
});

test('viewing payslips keeps the payroll dropdown open and highlights My Payslips', function () {
    $html = $this->withSession(employeeSidebarSession())
        ->get(route('employee.payroll.index'))
        ->assertStatus(200)
        ->getContent();

    expect($html)->toContain('class="collapse show" id="empPayrollSubmenu"');

    expect($html)->toContain('My Payslips');
});

test('employee loan, attendance, and tax pages load and keep the payroll dropdown open', function () {
    $session = employeeSidebarSession();

    foreach ([
        route('employee.loans.index') => 'My Loans',
        route('employee.attendance.index') => 'My Attendance',
        route('employee.tax.index') => 'Tax Documents',
    ] as $url => $heading) {
        $html = $this->withSession($session)
            ->get($url)
            ->assertStatus(200)
            ->assertSee($heading)
            ->getContent();

        expect($html)->toContain('class="collapse show" id="empPayrollSubmenu"');
    }
});

test('the employee loan page lists only the signed-in employee active and paid loans', function () {
    $employee = User::where('role_id', 1)->firstOrFail();
    $other = User::where('role_id', 1)->where('id', '!=', $employee->id)->first();

    Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'GSIS Conso-Loan',
        'principal_amount' => 12000,
        'monthly_amortization' => 1000,
        'running_balance' => 6000,
        'status' => 'Active',
    ]);

    Loan::create([
        'user_id' => $employee->id,
        'loan_type' => 'Pag-IBIG Multi-Purpose Loan',
        'principal_amount' => 5000,
        'monthly_amortization' => 500,
        'running_balance' => 0,
        'status' => 'Paid',
    ]);

    if ($other) {
        Loan::create([
            'user_id' => $other->id,
            'loan_type' => 'OTHER-EMPLOYEE-ONLY-LOAN',
            'principal_amount' => 20000,
            'monthly_amortization' => 2000,
            'running_balance' => 20000,
            'status' => 'Active',
        ]);
    }

    $response = $this->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
        'full_name' => trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')),
    ])->get(route('employee.loans.index'));

    $response->assertStatus(200);
    $response->assertSee('GSIS Conso-Loan');
    $response->assertSee('Pag-IBIG Multi-Purpose Loan');
    $response->assertDontSee('OTHER-EMPLOYEE-ONLY-LOAN');
});
