<?php

use App\Models\IncomeType;
use App\Models\User;
use App\Models\UserAllowance;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('allowance search matches first middle and last names', function () {
    $hr = User::where('role_id', 2)->firstOrFail();

    $this->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->get(route('payroll.allowances.index', ['search' => 'raiden']))
        ->assertSuccessful();
});

test('saving an allowance shows its badge on the employee row', function () {
    $hr = User::where('role_id', 2)->firstOrFail();
    $employee = User::where('role_id', 1)->where('status', 'active')->orderBy('last_name')->firstOrFail();
    $type = IncomeType::query()->where('is_active', true)->where('default_amount', '>', 0)->firstOrFail();
    $session = ['user_id' => $hr->id, 'role_id' => 2];

    $page = $this->withSession($session)
        ->get(route('payroll.allowances.index', ['search' => $employee->last_name]))
        ->assertSuccessful()
        ->getContent();

    expect($page)->toMatch(
        '/<form[^>]+action="[^"]*allowances\/'.$employee->id.'\/update"[^>]*>[\s\S]*id="incomeRepeater'.$employee->id.'"[\s\S]*<\/form>/'
    );

    $this->withSession($session)
        ->post(route('payroll.allowances.update', $employee), [
            'income_types' => [$type->id],
            'income_amounts' => [(string) $type->default_amount],
        ])
        ->assertRedirect(route('payroll.allowances.index'))
        ->assertSessionHas('success');

    expect(UserAllowance::where('user_id', $employee->id)->where('income_type_id', $type->id)->exists())->toBeTrue();

    $this->withSession($session)
        ->get(route('payroll.allowances.index', ['search' => $employee->last_name]))
        ->assertSuccessful()
        ->assertSee($type->name.' ('.number_format((float) $type->default_amount, 2).')', false);
});
