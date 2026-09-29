<?php

use App\Models\LateDeduction;
use App\Models\ServiceCredit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function serviceCreditHrSession(): array
{
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    return [
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ];
}

function serviceCreditEmployee(): User
{
    return User::where('role_id', 1)->where('status', 'active')->firstOrFail();
}

test('hr can grant service credits from the employee profile', function () {
    $employee = serviceCreditEmployee();

    $this->withSession(serviceCreditHrSession())
        ->post(route('hr.service_credits.store', $employee->id), [
            'transaction_date' => '2026-06-01',
            'description' => 'Brigada Eskwela',
            'days' => 1.5,
        ])
        ->assertRedirect(route('hr.view_profile', $employee->id));

    $employee->unsetRelation('serviceCredits');

    expect((float) $employee->available_credits)->toBe(1.5);
    expect(ServiceCredit::where('user_id', $employee->id)->where('type', 'earned')->where('description', 'Brigada Eskwela')->exists())->toBeTrue();

    $this->withSession(serviceCreditHrSession())
        ->get(route('hr.view_profile', $employee->id))
        ->assertSuccessful()
        ->assertSee('Service Credits')
        ->assertSee('Grant Credits')
        ->assertSee('Brigada Eskwela')
        ->assertSee('1.5');
});

test('an employee cannot grant service credits', function () {
    $employee = serviceCreditEmployee();

    $this->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->post(route('hr.service_credits.store', $employee->id), [
        'transaction_date' => '2026-06-01',
        'description' => 'Should Not Save',
        'days' => 1,
    ])->assertRedirect(route('dashboard'));

    expect(ServiceCredit::where('description', 'Should Not Save')->exists())->toBeFalse();
});

test('the leave page shows the employee service credit balance', function () {
    $employee = serviceCreditEmployee();

    ServiceCredit::create([
        'user_id' => $employee->id,
        'transaction_date' => '2026-06-01',
        'description' => 'School Camp',
        'type' => 'earned',
        'days' => 2,
    ]);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
        'full_name' => $employee->first_name.' '.$employee->last_name,
    ])->get(route('leave.index'))
        ->assertSuccessful()
        ->assertSee('Available Service Credits')
        ->assertSee('2.0 days')
        ->assertSee('Service credits can be used to offset unexcused absences to prevent salary deductions.');
});

test('attendance lists available credits and offsets absences before the salary deduction', function () {
    $employee = serviceCreditEmployee();

    ServiceCredit::create([
        'user_id' => $employee->id,
        'transaction_date' => '2026-06-01',
        'description' => 'In-service training',
        'type' => 'earned',
        'days' => 1,
    ]);

    $this->withSession(serviceCreditHrSession())
        ->get(route('payroll.attendance.index', [
            'month' => 3,
            'year' => 2099,
            'search' => $employee->last_name ?: $employee->first_name,
        ]))
        ->assertSuccessful()
        ->assertSee('Available Credits: 1.0');

    $this->withSession(serviceCreditHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 3,
            'period_year' => 2099,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 0,
                    'absent_days' => 1.5,
                    'dates_absent' => json_encode([
                        '2099-03-02' => 1,
                        '2099-03-03' => 0.5,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    $record = LateDeduction::where('user_id', $employee->id)
        ->where('payroll_period', '2099-03')
        ->first();

    $used = ServiceCredit::where('user_id', $employee->id)
        ->where('payroll_period', '2099-03')
        ->where('type', 'used')
        ->first();

    expect($record)->not->toBeNull()
        ->and((float) $record->unexcused_absences)->toBe(1.5)
        ->and((float) $record->absence_deduction_amount)->toBe(round($employee->base_salary / 22 * 0.5, 2))
        ->and($used)->not->toBeNull()
        ->and((float) $used->days)->toBe(1.0);

    $this->withSession(serviceCreditHrSession())
        ->post(route('payroll.attendance.save'), [
            'period_month' => 3,
            'period_year' => 2099,
            'attendance' => [
                $employee->id => [
                    'user_id' => $employee->id,
                    'minutes_late' => 0,
                    'absent_days' => 1.5,
                    'dates_absent' => json_encode([
                        '2099-03-02' => 1,
                        '2099-03-03' => 0.5,
                    ]),
                ],
            ],
        ])
        ->assertRedirect();

    expect(ServiceCredit::where('user_id', $employee->id)->where('payroll_period', '2099-03')->where('type', 'used')->count())->toBe(1);
    expect((float) $employee->fresh()->available_credits)->toBe(0.0);
});
