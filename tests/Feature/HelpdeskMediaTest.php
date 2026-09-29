<?php

use App\Models\HrMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

test('the helpdesk form offers voice pictures and documents', function () {
    $employee = User::where('role_id', 1)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('employee.chat'))
        ->assertSuccessful()
        ->assertSee('You can also add voice messages, pictures, and documents.', false)
        ->assertSee('id="chatForm"', false)
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('id="recordButton"', false)
        ->assertSee('name="attachment"', false);
});

test('an employee can send a picture to the helpdesk', function () {
    Storage::fake('public');
    $employee = User::where('role_id', 1)->firstOrFail();
    $hr = User::where('role_id', 2)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('employee.chat'))
        ->post(route('employee.chat.send'), [
            'message' => 'Please update my photo.',
            'attachment' => UploadedFile::fake()->image('id-photo.jpg'),
        ])
        ->assertRedirect(route('employee.chat'));

    $message = HrMessage::where('employee_id', $employee->id)
        ->where('message', 'Please update my photo.')
        ->first();

    expect($message)->not->toBeNull()
        ->and($message->attachment_type)->toBe('image')
        ->and($message->attachment_path)->toStartWith('helpdesk/');

    Storage::disk('public')->assertExists($message->attachment_path);

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->get(route('hr.chat.show', $employee->id))
        ->assertSuccessful()
        ->assertSee('storage/'.$message->attachment_path, false)
        ->assertSee('Please update my photo.', false);

    $this->actingAs($hr)->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->get(route('hr.chat.inbox'))
        ->assertSuccessful()
        ->assertSee('Please update my photo.', false);
});

test('an employee can send a voice note without text', function () {
    Storage::fake('public');
    $employee = User::where('role_id', 1)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('employee.chat'))
        ->post(route('employee.chat.send'), [
            'voice_message' => UploadedFile::fake()->create('voice.webm', 40, 'audio/webm'),
        ])
        ->assertRedirect(route('employee.chat'));

    $message = HrMessage::where('employee_id', $employee->id)
        ->where('sender_id', $employee->id)
        ->where('attachment_type', 'audio')
        ->latest('id')
        ->first();

    expect($message)->not->toBeNull()
        ->and($message->message)->toBeNull()
        ->and($message->attachment_path)->toStartWith('helpdesk/audio/');

    Storage::disk('public')->assertExists($message->attachment_path);

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('employee.chat'))
        ->assertSuccessful()
        ->assertSee('<audio controls', false)
        ->assertSee('audio/webm', false);
});

test('an employee can send a document to the helpdesk', function () {
    Storage::fake('public');
    $employee = User::where('role_id', 1)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('employee.chat'))
        ->post(route('employee.chat.send'), [
            'message' => 'Updated PDS copy',
            'attachment' => UploadedFile::fake()->create('pds.pdf', 80, 'application/pdf'),
        ])
        ->assertRedirect(route('employee.chat'));

    $message = HrMessage::where('employee_id', $employee->id)
        ->where('message', 'Updated PDS copy')
        ->first();

    expect($message)->not->toBeNull()
        ->and($message->attachment_type)->toBe('document');

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->get(route('employee.chat'))
        ->assertSuccessful()
        ->assertSee('View Document', false);
});

test('a helpdesk message needs text or a file', function () {
    $employee = User::where('role_id', 1)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('employee.chat'))
        ->post(route('employee.chat.send'), [
            'message' => '',
        ])
        ->assertRedirect(route('employee.chat'))
        ->assertSessionHasErrors('message');
});

test('an executable attachment is rejected', function () {
    Storage::fake('public');
    $employee = User::where('role_id', 1)->firstOrFail();

    $this->actingAs($employee)->withSession([
        'user_id' => $employee->id,
        'role_id' => 1,
    ])->from(route('employee.chat'))
        ->post(route('employee.chat.send'), [
            'attachment' => UploadedFile::fake()->create('payload.exe', 10, 'application/x-msdownload'),
        ])
        ->assertRedirect(route('employee.chat'))
        ->assertSessionHasErrors('attachment');
});
