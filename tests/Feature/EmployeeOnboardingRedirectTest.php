<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('creating an employee stays on personnel requisitions', function () {
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    $this->withSession([
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ])->post(route('employees.store'), [
        'first_name' => 'Test',
        'last_name' => 'Hire',
        'middle_initial' => 'A',
        'username' => 'test.hire.'.uniqid(),
        'password' => '1234',
        'employee_type' => '1',
    ])->assertRedirect(route('requisitions.index'))
        ->assertSessionHas('success');
});

test('the new employee form returns to personnel requisitions', function () {
    $hrUser = User::whereIn('role_id', [2, 3])->first() ?? User::first();

    $this->withSession([
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ])->get(route('employees.create'))
        ->assertSuccessful()
        ->assertSee(route('requisitions.index'), false)
        ->assertSee('Personnel Requisitions');
});
