<?php

use App\Models\User;

test('hr staff profiling page loads successfully with 200 status', function () {
    $hrUser = User::whereIn('role_id', [2, 3])->first();

    if (! $hrUser) {
        $hrUser = User::first();
    }

    $response = $this->withSession([
        'user_id' => $hrUser ? $hrUser->id : 1,
        'role_id' => 2,
    ])->get(route('hr.staff_profiling'));

    $response->assertStatus(200);
});
