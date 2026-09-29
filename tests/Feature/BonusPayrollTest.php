<?php

use App\Enums\PayrollType;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function payrollHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

function generatePayroll(string $payrollType, int $month): PayrollPeriod
{
    test()->withSession(payrollHrSession())->post(route('hr.payroll.store'), [
        'fund_cluster' => '01',
        'payroll_type' => $payrollType,
        'period_month' => $month,
        'period_year' => 2031,
        'description' => 'Automated test run',
    ])->assertSessionHasNoErrors();

    return PayrollPeriod::where('period_year', 2031)->where('period_month', $month)->firstOrFail();
}

test('payroll periods default to regular monthly', function () {
    $period = PayrollPeriod::create([
        'fund_cluster' => '01',
        'period_month' => 6,
        'period_year' => 2031,
        'status' => 'DRAFT',
    ]);

    expect($period->payroll_type)->toBe(PayrollType::Regular)
        ->and($period->fresh()->payroll_type)->toBe(PayrollType::Regular);
});

test('an unknown payroll type is rejected', function () {
    $this->withSession(payrollHrSession())->post(route('hr.payroll.store'), [
        'fund_cluster' => '01',
        'payroll_type' => 'Quarterly Bonus',
        'period_month' => 7,
        'period_year' => 2031,
    ])->assertSessionHasErrors('payroll_type');
});

test('a mid-year bonus run pays basic salary with no statutory deductions', function () {
    $period = generatePayroll(PayrollType::MidYearBonus->value, 8);

    expect($period->payroll_type)->toBe(PayrollType::MidYearBonus);

    $records = PayrollRecord::where('payroll_period_id', $period->id)->get();

    expect($records)->not->toBeEmpty();

    foreach ($records as $record) {
        expect((float) $record->gsis_premium)->toBe(0.0)
            ->and((float) $record->philhealth_premium)->toBe(0.0)
            ->and((float) $record->pagibig_premium)->toBe(0.0)
            ->and((float) $record->absences_amount)->toBe(0.0)
            ->and((float) $record->late_deduction)->toBe(0.0)
            ->and((float) $record->loan_amortization)->toBe(0.0)
            ->and((float) $record->pera_amount)->toBe(0.0)
            ->and((float) $record->gross_earned)->toBe((float) $record->basic_rate)
            ->and((float) $record->net_amount)->toBe((float) $record->gross_earned - (float) $record->tax_withheld);
    }
});

test('a year-end bonus run adds the 5000 cash gift on top of basic salary', function () {
    $period = generatePayroll(PayrollType::YearEndBonus->value, 9);

    $records = PayrollRecord::where('payroll_period_id', $period->id)->get();

    expect($records)->not->toBeEmpty();

    foreach ($records as $record) {
        expect((float) $record->gross_earned)->toBe((float) $record->basic_rate + 5000.0)
            ->and((float) $record->total_deductions)->toBe((float) $record->tax_withheld);
    }
});

test('a regular run still applies statutory deductions', function () {
    $period = generatePayroll(PayrollType::Regular->value, 10);

    expect($period->payroll_type)->toBe(PayrollType::Regular);

    $record = PayrollRecord::where('payroll_period_id', $period->id)
        ->where('is_full_lwop', false)
        ->where('basic_rate', '>', 0)
        ->first();

    expect($record)->not->toBeNull()
        ->and((float) $record->gsis_premium)->toBeGreaterThan(0)
        ->and((float) $record->philhealth_premium)->toBeGreaterThan(0)
        ->and((float) $record->pagibig_premium)->toBeGreaterThan(0);
});

test('bonus tax exempts the first 90000 and taxes the excess at 20 percent', function () {
    $service = new PayrollCalculationService;

    expect($service->calculateBonusTax(50000))->toBe(0.0)
        ->and($service->calculateBonusTax(90000))->toBe(0.0)
        ->and($service->calculateBonusTax(100000))->toBe(2000.0);
});

test('the master sheet shows the payroll type and mutes zeroed deduction columns', function () {
    $period = generatePayroll(PayrollType::YearEndBonus->value, 11);

    $response = $this->withSession(payrollHrSession())->get(route('hr.payroll.show', $period->id));

    $response->assertStatus(200);
    $response->assertSee('Year-End Bonus');
    $response->assertSee('statutory deductions do not apply', false);
    $response->assertSee('text-muted opacity-50', false);
});
