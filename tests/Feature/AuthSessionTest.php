<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Auth;

test('logs out and properly destroys session via POST /logout', function () {
    $user = User::first() ?? User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'test_user_session',
        'password' => bcrypt('password'),
        'role_id' => 1,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'user_id' => $user->id,
            'username' => $user->username,
            'role_id' => $user->role_id,
            'full_name' => 'Test User',
        ])
        ->post(route('logout'));

    $response->assertRedirect('/');
    $response->assertSessionMissing('user_id');
    $response->assertSessionMissing('username');
    $response->assertSessionMissing('role_id');
    $response->assertSessionMissing('full_name');

    expect(Auth::check())->toBeFalse();
});

test('logs out and destroys session via GET /logout', function () {
    $user = User::first() ?? User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'test_user_session_get',
        'password' => bcrypt('password'),
        'role_id' => 1,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'user_id' => $user->id,
            'username' => $user->username,
            'role_id' => $user->role_id,
        ])
        ->get(route('logout'));

    $response->assertRedirect('/');
    $response->assertSessionMissing('user_id');
    $response->assertSessionMissing('username');

    expect(Auth::check())->toBeFalse();
});

test('redirects to login when accessing protected dashboard with destroyed session', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('logout is exempt from CSRF verification preventing 419 on expired session', function () {
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post(route('logout'));

    $response->assertRedirect('/');
    $response->assertStatus(302);
});
