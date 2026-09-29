<?php

use App\Enums\PositionCategory;
use App\Models\LeaveCreditBalance;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

uses(DatabaseTransactions::class);

test('the leave form shows the official salary grade amount and ignores a typed salary', function () {
    $employee = User::where('role_id', 1)->where('status', 'active')->firstOrFail();

    $position = Position::create([
        'position_name' => 'LEAVE FORM CLERK',
        'category' => PositionCategory::NonTeaching,
        'salary_grade' => 11,
    ]);

    DB::table('salary_grades')->updateOrInsert(
        ['grade' => 11, 'step' => 1],
        ['amount' => 29999]
    );

    $employee->position_id = $position->id;
    $employee->step_increment = 1;
    $employee->save();

    LeaveCreditBalance::updateOrCreate(
        ['user_id' => $employee->id],
        ['vl_balance' => 10, 'sl_balance' => 10, 'service_credits' => 0, 'seminar_credits' => 0]
    );

    $session = [
        'user_id' => $employee->id,
        'role_id' => 1,
    ];

    $this->actingAs($employee)->withSession($session)
        ->get(route('leave.index'))
        ->assertSuccessful()
        ->assertSee('name="position" id="position" class="form-control form-control-sm text-uppercase bg-light" style="color: #1A3E6F;" value="LEAVE FORM CLERK" readonly', false)
        ->assertSee('name="salary" id="salary" class="form-control form-control-sm bg-light" value="29,999.00" readonly', false);

    $this->actingAs($employee)->withSession($session)
        ->post(route('leave.store'), [
            'date_of_filing' => '2026-09-27',
            'position' => 'FORGED TITLE',
            'salary' => '1.00',
            'leave_type' => 'Vacation Leave',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-06',
            'commutation' => 'Not Requested',
        ])
        ->assertSessionHas('success');

    $saved = DB::table('leave_applications')
        ->where('user_id', $employee->id)
        ->orderByDesc('id')
        ->first();

    expect($saved->position)->toBe('LEAVE FORM CLERK')
        ->and($saved->salary)->toBe('29,999.00');
});
