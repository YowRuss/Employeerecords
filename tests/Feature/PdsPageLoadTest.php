<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('my pds does not embed the full school list', function () {
    $employee = User::where('role_id', 1)->firstOrFail();
    $school = School::query()->value('school_name');

    $html = $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('pds.edit'))
        ->assertSuccessful()
        ->assertSee('Type a school name...', false)
        ->getContent();

    expect(substr_count($html, '<option'))->toBeLessThan(400);
    if ($school) {
        expect(substr_count($html, e($school)))->toBeLessThan(3);
    }
});

test('school search returns a short match list', function () {
    $employee = User::where('role_id', 1)->firstOrFail();
    $school = School::query()->first();

    if (! $school) {
        $this->markTestSkipped('No schools are available to search.');
    }

    $term = mb_substr($school->school_name, 0, 8);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->getJson(route('api.schools.search', ['q' => $term]))
        ->assertSuccessful()
        ->assertJsonFragment(['id' => $school->school_id, 'text' => $school->school_name]);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->getJson(route('api.schools.search', ['q' => 'a']))
        ->assertSuccessful()
        ->assertExactJson([]);
});
