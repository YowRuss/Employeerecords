<?php

use App\Enums\PositionCategory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('institutional reports list each employee under their assigned position', function () {
    $hr = User::where('role_id', 2)->firstOrFail();
    $employee = User::where('role_id', 1)->where('status', 'active')->firstOrFail();

    $position = Position::create([
        'position_name' => 'REPORT CLERK III',
        'category' => PositionCategory::NonTeaching,
        'salary_grade' => 4,
    ]);

    $employee->position_id = $position->id;
    $employee->save();

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->get(route('hr.reports.index'))
        ->assertSuccessful()
        ->assertSee('text-accent', false)
        ->assertSee('btn-accent', false)
        ->assertSee('REPORT CLERK III', false)
        ->assertDontSee('Report Clerk Iii', false);
});
