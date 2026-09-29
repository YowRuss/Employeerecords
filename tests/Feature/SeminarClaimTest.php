<?php

use App\Enums\PositionCategory;
use App\Models\LeaveCreditLog;
use App\Models\Seminar;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('a teaching employee can claim a seminar from my seminars', function () {
    $employee = User::where('role_id', 1)
        ->whereHas('position', fn ($query) => $query->where('category', PositionCategory::Teaching))
        ->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('leave.index'))
        ->post(route('leave.seminar.store'), [
            'title' => 'INSET Training',
            'date_attended' => '2026-09-20',
            'hours' => 8,
        ])
        ->assertRedirect(route('leave.index'))
        ->assertSessionHas('success');

    $seminar = Seminar::where('user_id', $employee->id)->where('title', 'INSET Training')->first();

    expect($seminar)->not->toBeNull()
        ->and((string) $seminar->date_attended)->toStartWith('2026-09-20')
        ->and($seminar->status)->toBe('PENDING');
});

test('approving a seminar records seminar credits in the ledger', function () {
    $hr = User::whereIn('role_id', [2, 3])->firstOrFail();
    $employee = User::where('role_id', 1)
        ->whereHas('position', fn ($query) => $query->where('category', PositionCategory::Teaching))
        ->firstOrFail();

    $seminar = Seminar::create([
        'user_id' => $employee->id,
        'title' => 'CASPTONE',
        'date_attended' => '2026-09-20',
        'hours' => 9,
        'status' => 'PENDING',
        'rate_applied' => 0,
        'credits_earned' => 0,
    ]);

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => (int) $hr->role_id,
    ])->post(route('hr.seminars.approve', $seminar->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    $seminar->refresh();
    $log = LeaveCreditLog::where('reference_type', 'seminars')
        ->where('reference_id', $seminar->id)
        ->first();

    expect($seminar->status)->toBe('APPROVED')
        ->and($log)->not->toBeNull()
        ->and($log->leave_bucket)->toBe('SEMINAR_CREDIT')
        ->and((float) $log->amount)->toBe((float) $seminar->credits_earned);
});
