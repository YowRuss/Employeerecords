<?php

use App\Enums\RemittanceAgency;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function remittanceHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

/**
 * Seed a period plus one record per employee, returning the employees used.
 *
 * @param  array<string, float>  $amounts
 * @return array{0: PayrollPeriod, 1: User, 2: User}
 */
function seedRemittancePeriod(string $status, array $amounts): array
{
    [$withDeductions, $withoutDeductions] = User::where('role_id', 1)->take(2)->get()->all();

    $period = PayrollPeriod::create([
        'fund_cluster' => '01',
        'period_month' => 3,
        'period_year' => 2032,
        'status' => $status,
    ]);

    PayrollRecord::create([
        'payroll_period_id' => $period->id,
        'user_id' => $withDeductions->id,
        'basic_rate' => 40000,
        'gross_earned' => 40000,
        'total_deductions' => 0,
        'net_amount' => 40000,
    ] + $amounts);

    // Zeroed across the board — must never appear on any agency report.
    PayrollRecord::create([
        'payroll_period_id' => $period->id,
        'user_id' => $withoutDeductions->id,
        'basic_rate' => 40000,
        'gross_earned' => 40000,
        'gsis_premium' => 0,
        'philhealth_premium' => 0,
        'pagibig_premium' => 0,
        'tax_withheld' => 0,
        'total_deductions' => 0,
        'net_amount' => 40000,
    ]);

    return [$period, $withDeductions, $withoutDeductions];
}

test('the remittance dashboard loads with no report generated', function () {
    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.index'));

    $response->assertStatus(200);
    $response->assertViewIs('payroll.remittances.index');
    $response->assertViewHas('results', null);
    $response->assertSee('No report generated yet');
});

test('an unknown agency is rejected', function () {
    $this->withSession(remittanceHrSession())->get(route('payroll.remittances.report', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => 'SSS',
    ]))->assertSessionHasErrors('agency');
});

test('a report lists only employees with a withholding for the selected agency', function () {
    [, $withDeductions, $withoutDeductions] = seedRemittancePeriod('FINALIZED', [
        'gsis_premium' => 3600,
        'philhealth_premium' => 1000,
        'pagibig_premium' => 200,
        'tax_withheld' => 0,
    ]);

    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.report', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => RemittanceAgency::Gsis->value,
    ]));

    $response->assertStatus(200);

    $results = $response->viewData('results');
    $userIds = $results->pluck('user_id')->all();

    expect($userIds)->toContain($withDeductions->id)
        ->and($userIds)->not->toContain($withoutDeductions->id)
        ->and((float) $results->firstWhere('user_id', $withDeductions->id)->amount_withheld)->toBe(3600.0);
});

test('BIR reports read the tax column and exclude zero-tax employees', function () {
    seedRemittancePeriod('FINALIZED', [
        'gsis_premium' => 3600,
        'philhealth_premium' => 1000,
        'pagibig_premium' => 200,
        'tax_withheld' => 0,
    ]);

    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.report', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => RemittanceAgency::Bir->value,
    ]));

    // Everyone in this period had zero tax withheld, so the report is empty.
    expect($response->viewData('results'))->toBeEmpty();
});

test('draft periods are excluded from remittance reports', function () {
    seedRemittancePeriod('DRAFT', [
        'gsis_premium' => 3600,
        'philhealth_premium' => 1000,
        'pagibig_premium' => 200,
        'tax_withheld' => 500,
    ]);

    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.report', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => RemittanceAgency::Gsis->value,
    ]));

    expect($response->viewData('results'))->toBeEmpty();
});

test('two finalized periods in the same month are combined into one row per employee', function () {
    [, $employee] = seedRemittancePeriod('FINALIZED', [
        'gsis_premium' => 0,
        'philhealth_premium' => 0,
        'pagibig_premium' => 0,
        'tax_withheld' => 1200,
    ]);

    $bonusPeriod = PayrollPeriod::create([
        'fund_cluster' => '01',
        'payroll_type' => 'Year-End Bonus',
        'period_month' => 3,
        'period_year' => 2032,
        'status' => 'FINALIZED',
    ]);

    PayrollRecord::create([
        'payroll_period_id' => $bonusPeriod->id,
        'user_id' => $employee->id,
        'basic_rate' => 40000,
        'gross_earned' => 45000,
        'tax_withheld' => 800,
        'total_deductions' => 800,
        'net_amount' => 44200,
    ]);

    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.report', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => RemittanceAgency::Bir->value,
    ]));

    $rows = $response->viewData('results')->where('user_id', $employee->id);

    expect($rows)->toHaveCount(1)
        ->and((float) $rows->first()->amount_withheld)->toBe(2000.0);
});

test('the CSV export streams the filtered dataset with a total row', function () {
    seedRemittancePeriod('FINALIZED', [
        'gsis_premium' => 3600,
        'philhealth_premium' => 1000,
        'pagibig_premium' => 200,
        'tax_withheld' => 0,
    ]);

    $response = $this->withSession(remittanceHrSession())->get(route('payroll.remittances.export', [
        'period_month' => 3,
        'period_year' => 2032,
        'agency' => RemittanceAgency::PagIbig->value,
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload('PagIBIG_Remittance_March_2032.csv');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Pag-IBIG Remittance Report')
        ->toContain('Pag-IBIG MID No.')
        ->toContain('March 2032')
        ->toContain('TOTAL')
        ->toContain('200.00');
});
