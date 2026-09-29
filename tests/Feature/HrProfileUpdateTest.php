<?php

use App\Enums\PositionCategory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

function profileSession(User $user): array
{
    return [
        'user_id' => $user->id,
        'role_id' => (int) $user->role_id,
        'username' => $user->username,
        'full_name' => trim($user->first_name.' '.$user->last_name),
    ];
}

test('hr admin can edit name username and position on their own profile', function () {
    $hr = User::where('role_id', 2)->firstOrFail();
    $position = Position::create([
        'position_name' => 'HR Profile Clerk',
        'category' => PositionCategory::NonTeaching,
    ]);

    $this->actingAs($hr)->withSession(profileSession($hr))
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee('name="first_name"', false)
        ->assertSee('name="username"', false)
        ->assertSee('name="position_id"', false)
        ->assertSee('name="emergency_contact_person"', false)
        ->assertSee('Save Changes', false)
        ->assertDontSee('Contact HR to change your official name.', false);

    $this->actingAs($hr)->withSession(profileSession($hr))
        ->put(route('hr.profile.update'), [
            'first_name' => 'Helena',
            'middle_name' => 'Reyes',
            'last_name' => 'Santos',
            'suffix' => 'Jr',
            'username' => 'hr_profile_helena',
            'position_id' => $position->id,
            'emergency_contact_person' => 'Ana Santos',
            'emergency_contact_number' => '09171234567',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Profile updated successfully.');

    $fresh = $hr->fresh();

    expect($fresh->first_name)->toBe('HELENA')
        ->and($fresh->middle_name)->toBe('REYES')
        ->and($fresh->last_name)->toBe('SANTOS')
        ->and($fresh->suffix)->toBe('JR')
        ->and($fresh->username)->toBe('hr_profile_helena')
        ->and((int) $fresh->position_id)->toBe($position->id)
        ->and($fresh->emergency_contact_person)->toBe('ANA SANTOS')
        ->and($fresh->emergency_contact_number)->toBe('09171234567')
        ->and((int) $fresh->employee_type)->toBe(0);
});

test('employees cannot change their official name from the profile form', function () {
    $employee = User::where('role_id', 1)->where('status', 'active')->firstOrFail();
    $originalFirst = $employee->first_name;

    $this->actingAs($employee)->withSession(profileSession($employee))
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee('Contact HR to change your official name.', false)
        ->assertDontSee('name="first_name"', false);

    $this->actingAs($employee)->withSession(profileSession($employee))
        ->put(route('hr.profile.update'), [
            'first_name' => 'Changed',
            'last_name' => 'Name',
            'username' => 'hacked_username',
            'emergency_contact_person' => 'Maria Cruz',
            'emergency_contact_number' => '09180001111',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $employee->fresh();

    expect($fresh->first_name)->toBe($originalFirst)
        ->and($fresh->username)->toBe($employee->username)
        ->and($fresh->emergency_contact_person)->toBe('MARIA CRUZ');
});
