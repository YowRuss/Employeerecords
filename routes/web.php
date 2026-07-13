<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Position Route for Admin
Route::post('/positions/store', [DashboardController::class, 'storePosition'])->name('positions.store');

// Employee Management Routes (For HR and Admin)
Route::get('/employees/create', [DashboardController::class, 'createEmployee'])->name('employees.create');
Route::post('/employees/store', [DashboardController::class, 'storeEmployee'])->name('employees.store');

// ==========================================
// Employee Routes (PDS - Multi-Tab System)
// ==========================================
Route::get('/my-pds', [App\Http\Controllers\PdsController::class, 'editPds'])->name('pds.edit');
Route::post('/my-pds/personal-info', [App\Http\Controllers\PdsController::class, 'updatePersonalInfo'])->name('pds.update_personal_info');
Route::post('/my-pds/family-background', [App\Http\Controllers\PdsController::class, 'updateFamilyBackground'])->name('pds.update_family_background'); // <--- NEW LINE
Route::post('/my-pds/child/add', [App\Http\Controllers\PdsController::class, 'addChild'])->name('pds.add_child');
Route::post('/my-pds/education/add', [App\Http\Controllers\PdsController::class, 'addEducation'])->name('pds.add_education');
Route::post('/my-pds/signature', [App\Http\Controllers\PdsController::class, 'saveSignature'])->name('pds.signature');
Route::post('/my-pds/add-eligibility', [App\Http\Controllers\PdsController::class, 'addEligibility'])->name('pds.add_eligibility');
Route::post('/my-pds/add-work-experience', [App\Http\Controllers\PdsController::class, 'addWorkExperience'])->name('pds.add_work_experience');
Route::post('/my-pds/add-voluntary', [App\Http\Controllers\PdsController::class, 'addVoluntaryWork'])->name('pds.add_voluntary');
Route::post('/my-pds/add-learning', [App\Http\Controllers\PdsController::class, 'addLearning'])->name('pds.add_learning');
Route::post('/my-pds/add-other-info', [App\Http\Controllers\PdsController::class, 'addOtherInfo'])->name('pds.add_other_info');
Route::post('/my-pds/update-questionnaire', [App\Http\Controllers\PdsController::class, 'updateQuestionnaire'])->name('pds.update_questionnaire');
Route::post('/my-pds/add-reference', [App\Http\Controllers\PdsController::class, 'addReference'])->name('pds.add_reference');
Route::post('/my-pds/update-page4-details', [App\Http\Controllers\PdsController::class, 'updatePage4Details'])->name('pds.update_page4_details');
Route::post('/my-pds/delete-record/{table}/{id}', [App\Http\Controllers\PdsController::class, 'deleteRecord'])->name('pds.delete_record');
Route::get('/my-pds/print', [App\Http\Controllers\PdsController::class, 'printPds'])->name('pds.print');

// Employee Routes (SALN)
Route::get('/my-saln', [App\Http\Controllers\SalnController::class, 'editSaln'])->name('saln.edit');
Route::post('/my-saln', [App\Http\Controllers\SalnController::class, 'updateSaln'])->name('saln.update');
// SALN Routes
Route::get('/my-saln', [App\Http\Controllers\SalnController::class, 'index'])->name('saln.index');
Route::post('/my-saln/update-info', [App\Http\Controllers\SalnController::class, 'updateInfo'])->name('saln.update_info');
Route::post('/my-saln/add-child', [App\Http\Controllers\SalnController::class, 'addChild'])->name('saln.add_child');
Route::post('/my-saln/add-real-property', [App\Http\Controllers\SalnController::class, 'addRealProperty'])->name('saln.add_real_property');
Route::post('/my-saln/add-personal-property', [App\Http\Controllers\SalnController::class, 'addPersonalProperty'])->name('saln.add_personal_property');
Route::post('/my-saln/add-liability', [App\Http\Controllers\SalnController::class, 'addLiability'])->name('saln.add_liability');
Route::post('/my-saln/add-business', [App\Http\Controllers\SalnController::class, 'addBusiness'])->name('saln.add_business');
Route::post('/my-saln/add-relative', [App\Http\Controllers\SalnController::class, 'addRelative'])->name('saln.add_relative');
Route::post('/my-saln/delete/{table}/{id}', [App\Http\Controllers\SalnController::class, 'deleteRecord'])->name('saln.delete_record');

// HR Management Routes
Route::get('/hr/employee/{id}/pds', [App\Http\Controllers\HrController::class, 'viewPds'])->name('hr.view_pds');
Route::get('/hr/employee/{id}/saln', [App\Http\Controllers\HrController::class, 'viewSaln'])->name('hr.view_saln');
Route::get('/hr/staff-profiling', [App\Http\Controllers\HrController::class, 'staffProfiling'])->name('hr.staff_profiling');
// Add this to your routes/web.php
Route::post('/hr/positions/store', [App\Http\Controllers\PositionController::class, 'store'])->name('hr.positions.store');
Route::post('/hr/positions/store', [App\Http\Controllers\PositionController::class, 'store'])->name('hr.positions.store');
Route::post('/hr/positions/delete/{id}', [App\Http\Controllers\PositionController::class, 'destroy'])->name('hr.positions.destroy');


// Employee Routes (Leave Requests)
Route::get('/leave-requests', [App\Http\Controllers\LeaveController::class, 'index'])->name('leave.index');
Route::post('/leave-requests', [App\Http\Controllers\LeaveController::class, 'store'])->name('leave.store');
Route::post('/my-leave/delete/{id}', [App\Http\Controllers\LeaveController::class, 'destroy'])->name('leave.destroy');

// User Profile Routes
Route::get('/my-profile', [App\Http\Controllers\ProfileController::class, 'editProfile'])->name('profile.edit');
Route::post('/my-profile', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');

// Leave Monitoring & Approvals (HR & Principal)
Route::get('/manage-leaves', [App\Http\Controllers\LeaveController::class, 'monitorLeaves'])->name('leaves.monitor');
Route::get('/manage-leaves/view/{id}', [App\Http\Controllers\LeaveController::class, 'viewLeave'])->name('leaves.view');
Route::post('/manage-leaves/{id}', [App\Http\Controllers\LeaveController::class, 'updateLeaveStatus'])->name('leaves.update_status');

// ---------------------------------------------
// HR LEAVE MANAGEMENT ROUTES
// ---------------------------------------------
// Change this line in your routes/web.php:
Route::get('/hr/leave-monitoring', [App\Http\Controllers\LeaveController::class, 'hrIndex'])->name('hr.leave.index');
Route::post('/hr/leave/approve/{id}', [App\Http\Controllers\LeaveController::class, 'hrApprove'])->name('hr.leave.approve');
Route::post('/hr/leave/reject/{id}', [App\Http\Controllers\LeaveController::class, 'hrReject'])->name('hr.leave.reject');

// HR Routes (Service Records)
Route::get('/hr/service-record/{user_id}', [App\Http\Controllers\ServiceRecordController::class, 'hrIndex'])->name('hr.service_record.index');
Route::post('/hr/service-record/store/{user_id}', [App\Http\Controllers\ServiceRecordController::class, 'hrStore'])->name('hr.service_record.store');
Route::post('/hr/service-record/delete/{id}', [App\Http\Controllers\ServiceRecordController::class, 'hrDestroy'])->name('hr.service_record.destroy');
//Route::get('/hr/employee/{id}/service-record', [App\Http\Controllers\HrController::class, 'viewServiceRecord'])->name('hr.view_service_record');
//Route::post('/hr/service-record/store', [App\Http\Controllers\HrController::class, 'storeServiceRecord'])->name('hr.store_service_record');
// HR Service Records Directory (Shows list of employees)
Route::get('/hr/service-records-directory', [App\Http\Controllers\ServiceRecordController::class, 'hrDirectory'])->name('hr.service_record.directory');

// Employee Routes (Service Record)
Route::get('/my-service-record', [App\Http\Controllers\EmployeeController::class, 'myServiceRecord'])->name('employee.service_record');
// Service Record Routes
Route::get('/my-service-record', [App\Http\Controllers\ServiceRecordController::class, 'index'])->name('service_record.index');
//Route::post('/my-service-record/store', [App\Http\Controllers\ServiceRecordController::class, 'store'])->name('service_record.store');
//Route::post('/my-service-record/delete/{id}', [App\Http\Controllers\ServiceRecordController::class, 'destroy'])->name('service_record.destroy');

// Dashboard Route pointing to our new controller
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');