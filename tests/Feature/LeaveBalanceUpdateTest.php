<?php

use App\Models\LeaveCreditBalance;
use App\Models\LeaveCreditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('hr can set vacation and sick leave balances from employee balances', function () {
    $hr = User::whereIn('role_id', [2, 3])->firstOrFail();
    $employee = User::where('role_id', 1)->where('status', 'active')->firstOrFail();

    LeaveCreditBalance::updateOrCreate(
        ['user_id' => $employee->id],
        ['vl_balance' => 1, 'sl_balance' => 2, 'service_credits' => 0, 'seminar_credits' => 0]
    );

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => (int) $hr->role_id,
    ])->get(route('hr.leave.index'))
        ->assertSuccessful()
        ->assertSee('Vacation Leave (VL)', false)
        ->assertSee('Sick Leave (SL)', false)
        ->assertSee('id="adjustBalancesModal"', false)
        ->assertDontSee('Vacation and Sick leave credits do not apply to teachers', false);

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => (int) $hr->role_id,
    ])->from(route('hr.leave.index'))
        ->post(route('hr.leaves.updateBalances', $employee->id), [
            'vl_balance' => 5.25,
            'sl_balance' => 3.5,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $balance = LeaveCreditBalance::where('user_id', $employee->id)->first();

    expect((float) $balance->vl_balance)->toBe(5.25)
        ->and((float) $balance->sl_balance)->toBe(3.5)
        ->and(LeaveCreditLog::where('user_id', $employee->id)->where('leave_bucket', 'VL')->where('source', 'manual_adjustment')->exists())->toBeTrue();
});
