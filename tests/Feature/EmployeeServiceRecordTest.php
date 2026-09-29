<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('employee service record page loads HR-style document layout without print and add actions', function () {
    $userIdWithRecords = DB::table('service_records')->value('user_id');
    $employee = $userIdWithRecords ? User::find($userIdWithRecords) : (User::where('role_id', 1)->first() ?? User::first());

    $response = $this->withSession([
        'user_id' => $employee ? $employee->id : 1,
        'role_id' => 1,
        'full_name' => $employee ? $employee->first_name.' '.$employee->last_name : 'Test User',
    ])->get(route('service_record.index'));

    $response->assertStatus(200);
    $response->assertViewIs('employee.service_record');

    // Document header elements (matching HR service record)
    $response->assertSee('My Service Record');
    $response->assertSee('Republic of the Philippines');
    $response->assertSee('Department of Education');
    $response->assertSee('SERVICE RECORD');
    $response->assertSee('CERTIFIED CORRECT');

    // Strict restrictions: No printing and no adding of service record data
    $response->assertDontSee('Print Service Record');
    $response->assertDontSee('Print / Export PDF');
    $response->assertDontSee('Add Service Record Entry');
    $response->assertDontSee('id="addServiceRecordModal"', false);
    $response->assertDontSee('title="Delete Entry"');
});
