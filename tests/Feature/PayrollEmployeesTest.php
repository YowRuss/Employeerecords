<?php

use App\Models\User;

test('hr employee payroll directory page loads successfully with 200 status', function () {
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    $response = $this->withSession([
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ])->get(route('hr.payroll.employees'));

    $response->assertStatus(200);
    $response->assertViewIs('hr.payroll.employees');
    $response->assertViewHasAll(['employees', 'salaryMatrix', 'counts']);
    $response->assertSee('Employee Payroll Profiles');
    $response->assertSee(route('hr.payroll.employees'));
});

test('hr employee payroll directory supports filtering and search parameters', function () {
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    $response = $this->withSession([
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ])->get(route('hr.payroll.employees', [
        'category' => 'teaching',
        'eligibility' => 'all',
        'search' => 'test',
    ]));

    $response->assertStatus(200);
    $response->assertViewHas('currentCategory', 'teaching');
    $response->assertViewHas('currentSearch', 'test');
});
