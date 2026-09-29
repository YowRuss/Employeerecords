<?php

use App\Enums\PositionCategory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('each positions tab keeps its own page and stays selected', function () {
    $hr = User::whereIn('role_id', [2, 3])->firstOrFail();

    foreach (range(1, 11) as $index) {
        Position::create([
            'position_name' => 'TAB TEACHER '.$index,
            'category' => PositionCategory::Teaching,
            'salary_grade' => 11,
        ]);
        Position::create([
            'position_name' => 'TAB CLERK '.$index,
            'category' => PositionCategory::NonTeaching,
            'salary_grade' => 4,
        ]);
    }

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => (int) $hr->role_id,
    ])->get(route('hr.settings.positions_areas', ['non_teaching_page' => 2]))
        ->assertSuccessful()
        ->assertSee('class="tab-pane fade show active" id="pane-non-teaching"', false)
        ->assertSee('#pane-non-teaching', false)
        ->assertSee('#pane-teaching', false)
        ->assertSee('a.page-link[href]', false)
        ->assertDontSee('non_teaching_page=2&amp;', false)
        ->assertDontSee('&amp;non_teaching_page=2', false);
});
