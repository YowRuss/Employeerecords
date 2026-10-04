<?php

use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Bir2316Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeLoanController;
use App\Http\Controllers\EmployeePayrollController;
use App\Http\Controllers\EmployeeTaxController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\HrMessageController;
use App\Http\Controllers\HrReportController;
use App\Http\Controllers\HrSettingsController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveCreditController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PdsController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RemittanceController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SalaryGradeController;
use App\Http\Controllers\SalnController;
use App\Http\Controllers\SeminarController;
use App\Http\Controllers\ServiceCreditController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\Settings\DeductionController;
use App\Http\Controllers\Settings\IncomeTypeController;
use App\Http\Controllers\StepIncrementController;
use App\Models\PdsFather;
use App\Models\PdsMother;
use App\Models\PdsSpouse;
use Illuminate\Support\Facades\Route;

// Location API Routes
Route::get('/locations/regions', [LocationController::class, 'getRegions'])->name('locations.regions');
Route::get('/locations/provinces/{regionId}', [LocationController::class, 'getProvinces'])->name('locations.provinces');
Route::get('/locations/cities/{provinceId}', [LocationController::class, 'getCities'])->name('locations.cities');
Route::get('/locations/barangays/{cityId}', [LocationController::class, 'getBarangays'])->name('locations.barangays');

// Landing Page (Public Homepage)
Route::get('/', [LandingController::class, 'index'])->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.change.post');

// Forgot Password Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Position Route for Admin
Route::post('/positions/store', [DashboardController::class, 'storePosition'])->name('positions.store');

// Employee Management Routes (For HR and Admin)
Route::get('/employees/create', [DashboardController::class, 'createEmployee'])->name('employees.create');
Route::post('/employees/store', [DashboardController::class, 'storeEmployee'])->name('employees.store');
Route::get('/api/employees', [EmployeeController::class, 'apiGetEmployees'])->name('api.employees.get');

// HR Announcement Routes
Route::prefix('hr/announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/store', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/delete/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::get('/track/{id}', [AnnouncementController::class, 'tracking'])->name('announcements.track');
});

// HR Announcement Settings Routes
Route::prefix('hr/announcements/settings')->name('announcements.settings.')->group(function () {
    Route::get('/', [AnnouncementTypeController::class, 'index'])->name('index');
    Route::post('/store', [AnnouncementTypeController::class, 'store'])->name('store');
    Route::post('/update/{id}', [AnnouncementTypeController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [AnnouncementTypeController::class, 'destroy'])->name('destroy');
});

// HR Event Routes
Route::prefix('hr/events')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('events.index');
    Route::post('/store', [EventController::class, 'store'])->name('events.store');
    Route::post('/delete/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/track/{id}', [EventController::class, 'tracking'])->name('events.track');
    Route::post('/attendance/{id}', [EventController::class, 'markAttendance'])->name('events.attendance');
});

// HR Event Settings Routes
Route::prefix('hr/events/settings')->name('events.settings.')->group(function () {
    Route::get('/', [EventTypeController::class, 'index'])->name('index');
    Route::post('/store', [EventTypeController::class, 'store'])->name('store');
    Route::post('/update/{id}', [EventTypeController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [EventTypeController::class, 'destroy'])->name('destroy');
});

// HR Reports Route
Route::get('/hr/reports', [HrReportController::class, 'index'])->name('hr.reports.index');

// Employee Registration Route
Route::post('/events/{id}/register', [EventController::class, 'register'])->name('events.register');

// ==========================================
// Employee Routes (PDS - Multi-Tab System)
// ==========================================
Route::get('/my-pds', [PdsController::class, 'editPds'])->name('pds.edit');
Route::post('/my-pds', [PdsController::class, 'updatePersonalInfo'])->name('pds.update');
Route::post('/my-pds/personal-info', [PdsController::class, 'updatePersonalInfo'])->name('pds.update_personal_info');
Route::post('/my-pds/family-background', [PdsController::class, 'updateFamilyBackground'])->name('pds.update_family_background'); // <--- NEW LINE
Route::post('/my-pds/child/add', [PdsController::class, 'addChild'])->name('pds.add_child');
Route::post('/my-pds/child/update/{id}', [PdsController::class, 'updateChild'])->name('pds.update_child');
Route::post('/my-pds/education/add', [PdsController::class, 'addEducation'])->name('pds.add_education');
Route::post('/my-pds/education/update/{id}', [PdsController::class, 'updateEducation'])->name('pds.update_education');
Route::post('/my-pds/signature', [PdsController::class, 'saveSignature'])->name('pds.signature');
Route::post('/my-pds/add-eligibility', [PdsController::class, 'addEligibility'])->name('pds.add_eligibility');
Route::post('/my-pds/eligibility/update/{id}', [PdsController::class, 'updateEligibility'])->name('pds.update_eligibility');
Route::post('/my-pds/add-work-experience', [PdsController::class, 'addWorkExperience'])->name('pds.add_work_experience');
Route::post('/my-pds/work-experience/update/{id}', [PdsController::class, 'updateWorkExperience'])->name('pds.update_work_experience');
Route::post('/my-pds/add-voluntary', [PdsController::class, 'addVoluntaryWork'])->name('pds.add_voluntary');
Route::post('/my-pds/voluntary/update/{id}', [PdsController::class, 'updateVoluntaryWork'])->name('pds.update_voluntary');
Route::post('/my-pds/add-learning', [PdsController::class, 'addLearning'])->name('pds.add_learning');
Route::post('/my-pds/learning/update/{id}', [PdsController::class, 'updateLearning'])->name('pds.update_learning');
Route::post('/my-pds/add-other-info', [PdsController::class, 'addOtherInfo'])->name('pds.add_other_info');
Route::post('/my-pds/other-info/update/{id}', [PdsController::class, 'updateOtherInfo'])->name('pds.update_other_info');
Route::post('/my-pds/update-questionnaire', [PdsController::class, 'updateQuestionnaire'])->name('pds.update_questionnaire');
Route::post('/my-pds/add-reference', [PdsController::class, 'addReference'])->name('pds.add_reference');
Route::post('/my-pds/reference/update/{id}', [PdsController::class, 'updateReference'])->name('pds.update_reference');
Route::post('/my-pds/update-page4-details', [PdsController::class, 'updatePage4Details'])->name('pds.update_page4_details');
Route::post('/my-pds/delete-record/{table}/{id}', [PdsController::class, 'deleteRecord'])->name('pds.delete_record');
Route::get('/my-pds/print', [PdsController::class, 'printPds'])->name('pds.print');
Route::get('/pds/export/{id}', [PdsController::class, 'printPds'])->name('pds.export');
Route::get('/pds/document/{id}/{column}', [PdsController::class, 'downloadDocument'])->name('pds.document');
Route::post('/announcements/{id}/acknowledge', [AnnouncementController::class, 'acknowledge'])->name('announcements.acknowledge');

// SALN Routes
Route::get('/my-saln', [SalnController::class, 'index'])->name('saln.index');
Route::get('/my-saln/export', [SalnController::class, 'exportDocx'])->name('saln.export');
Route::post('/my-saln/update-info', [SalnController::class, 'updateInfo'])->name('saln.update_info');
Route::post('/my-saln/add-child', [SalnController::class, 'addChild'])->name('saln.add_child');
Route::post('/my-saln/add-real-property', [SalnController::class, 'addRealProperty'])->name('saln.add_real_property');
Route::post('/my-saln/add-personal-property', [SalnController::class, 'addPersonalProperty'])->name('saln.add_personal_property');
Route::post('/my-saln/add-liability', [SalnController::class, 'addLiability'])->name('saln.add_liability');
Route::post('/my-saln/add-business', [SalnController::class, 'addBusiness'])->name('saln.add_business');
Route::post('/my-saln/add-relative', [SalnController::class, 'addRelative'])->name('saln.add_relative');
Route::post('/my-saln/delete/{table}/{id}', [SalnController::class, 'deleteRecord'])->name('saln.delete_record');

// Employee Dedicated Views
Route::get('/my-announcements', [DashboardController::class, 'employeeAnnouncements'])->name('employee.announcements');
Route::get('/my-events', [DashboardController::class, 'employeeEvents'])->name('employee.events');

// HR Management Routes
Route::get('/hr/employee/{id}/pds', [HrController::class, 'viewPds'])->name('hr.view_pds');
Route::get('/hr/employee/{id}/saln', [HrController::class, 'viewSaln'])->name('hr.view_saln');
Route::get('/hr/staff-profiling', [HrController::class, 'staffProfiling'])->name('hr.staff_profiling');
Route::post('/hr/positions/store', [PositionController::class, 'store'])->name('hr.positions.store');
Route::post('/hr/positions/update/{id}', [PositionController::class, 'update'])->name('hr.positions.update');
Route::post('/hr/positions/delete/{id}', [PositionController::class, 'destroy'])->name('hr.positions.destroy');

Route::prefix('hr/job-postings')->group(function () {
    Route::get('/', [JobPostingController::class, 'index'])->name('hr.job_postings.index');
    Route::post('/store', [JobPostingController::class, 'store'])->name('hr.job_postings.store');
    Route::post('/{id}/update', [JobPostingController::class, 'update'])->name('hr.job_postings.update');
    Route::post('/{id}/delete', [JobPostingController::class, 'destroy'])->name('hr.job_postings.destroy');
});

// Payroll Settings
Route::get('/payroll/salary-settings', [SalaryGradeController::class, 'index'])->name('payroll.salary_settings');
Route::post('/payroll/salary-settings/{id}/update', [SalaryGradeController::class, 'update'])->name('payroll.salary_settings.update');

// Employee HR Helpdesk
Route::get('/my-hr-chat', [HrMessageController::class, 'employeeChat'])->name('employee.chat');
Route::post('/my-hr-chat/send', [HrMessageController::class, 'sendMessage'])->name('employee.chat.send');

// HR Helpdesk Management
Route::prefix('hr/messages')->group(function () {
    Route::get('/', [HrMessageController::class, 'hrInbox'])->name('hr.chat.inbox');
    Route::get('/{employee_id}', [HrMessageController::class, 'hrChat'])->name('hr.chat.show');
    Route::post('/{employee_id}/send', [HrMessageController::class, 'hrSendMessage'])->name('hr.chat.send');
});

// Public Hiring Portal (No login required)
Route::get('/careers', [JobApplicationController::class, 'index'])->name('careers.index');
Route::get('/careers/apply', [JobApplicationController::class, 'showForm'])->name('careers.form');
Route::post('/careers/apply', [JobApplicationController::class, 'apply'])->name('careers.apply');

// HR Application Management (Must be logged in as HR)
Route::prefix('hr/applications')->group(function () {
    Route::get('/', [JobApplicationController::class, 'hrIndex'])->name('hr.applications.index');
    Route::post('/{id}/status', [JobApplicationController::class, 'updateStatus'])->name('hr.applications.update');
    Route::get('/{id}/download-resume', [JobApplicationController::class, 'downloadResume'])->name('hr.applications.resume');
});

// Employee Routes (Leave Requests)
Route::get('/leave-requests', [LeaveController::class, 'index'])->name('leave.index');
Route::post('/leave-requests', [LeaveController::class, 'store'])->name('leave.store');
Route::post('/my-leave/delete/{id}', [LeaveController::class, 'destroy'])->name('leave.destroy');
Route::get('/my-leave/{id}/pdf', [LeaveController::class, 'exportLeavePDF'])->name('leave.export_pdf');
Route::middleware(['teaching.only'])->group(function () {
    Route::post('/my-leave/seminar/store', [SeminarController::class, 'store'])->name('leave.seminar.store');
});

// User Profile Routes
Route::get('/my-profile', [ProfileController::class, 'editProfile'])->name('profile.edit');
Route::post('/my-profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
Route::put('/hr/profile/update', [ProfileController::class, 'update'])->name('hr.profile.update');

// Leave Monitoring & Approvals (HR & Principal)
Route::get('/manage-leaves', [LeaveController::class, 'monitorLeaves'])->name('leaves.monitor');
Route::get('/manage-leaves/view/{id}', [LeaveController::class, 'viewLeave'])->name('leaves.view');
Route::post('/manage-leaves/{id}', [LeaveController::class, 'updateLeaveStatus'])->name('leaves.update_status');

// ---------------------------------------------
// HR LEAVE MANAGEMENT ROUTES
// ---------------------------------------------
// Change this line in your routes/web.php:
Route::get('/hr/leave-monitoring', [LeaveController::class, 'hrIndex'])->name('hr.leave.index');
Route::post('/hr/leave/{id}/status', [LeaveController::class, 'hrUpdateStatus'])->name('hr.leave.update_status');
Route::get('/hr/leave/{id}/print', [LeaveController::class, 'exportLeavePDF'])->name('hr.leave.print');
Route::post('/hr/leave-monitoring/balances/update/{id}', [LeaveCreditController::class, 'updateBalances'])->name('hr.leaves.updateBalances');
Route::post('/hr/credits/adjust/{user_id}', [LeaveCreditController::class, 'adjust'])->name('hr.credits.adjust');
Route::put('/hr/credits/settings', [LeaveCreditController::class, 'updateSettings'])->name('hr.credits.settings.update');
Route::post('/hr/seminars/{id}/approve', [SeminarController::class, 'approve'])->name('hr.seminars.approve');
Route::post('/hr/seminars/{id}/reject', [SeminarController::class, 'reject'])->name('hr.seminars.reject');

// HR Routes (Service Records)
Route::get('/hr/service-record/{user_id}', [ServiceRecordController::class, 'hrIndex'])->name('hr.service_record.show');
Route::post('/hr/service-record/store/{user_id}', [ServiceRecordController::class, 'hrStore'])->name('hr.service_record.store');
Route::post('/hr/service-record/delete/{id}', [ServiceRecordController::class, 'hrDestroy'])->name('hr.service_record.destroy');
Route::redirect('/hr/service-records-directory', '/hr/staff-profiling');

Route::get('/hr/service-records/{user_id}/print', [ServiceRecordController::class, 'printToExcel'])
    ->name('hr.service_records.print');
// routes/web.php
Route::get('/hr/staff-profiling', [HrController::class, 'staffProfiling'])->name('hr.staff_profiling');
Route::post('/hr/employee/{id}/update-position', [HrController::class, 'updatePosition'])->name('hr.update_position');
Route::post('/hr/employee/{id}/update-name', [HrController::class, 'updateOfficialName'])->name('hr.update_official_name');
// HR view employee profile route
Route::get('/hr/employee/{id}/profile', [HrController::class, 'viewProfile'])->name('hr.view_profile');
Route::post('/hr/employee/{id}/service-credits', [ServiceCreditController::class, 'store'])->name('hr.service_credits.store');
Route::post('/hr/employee/{id}/learning-area', [HrController::class, 'updateLearningArea'])->name('hr.update_learning_area');
Route::post('/hr/employees/promote', [HrController::class, 'promoteEmployee'])->name('hr.promote_employee');
Route::post('/hr/employees/offboard', [HrController::class, 'offboardEmployee'])->name('hr.offboard_employee');
Route::post('/hr/employees/{id}/reassign', [HrController::class, 'reassignEmployee'])->name('hr.reassign_employee');

// HR Requisitions Module
Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');

// HR Settings Module
Route::get('/hr/settings/positions-areas', [HrSettingsController::class, 'positionsAndAreas'])->name('hr.settings.positions_areas');
Route::post('/hr/settings/learning-areas/store', [HrSettingsController::class, 'storeLearningArea'])->name('hr.learning_areas.store');
Route::post('/hr/settings/learning-areas/update/{id}', [HrSettingsController::class, 'updateLearningArea'])->name('hr.learning_areas.update');
Route::post('/hr/settings/learning-areas/delete/{id}', [HrSettingsController::class, 'destroyLearningArea'])->name('hr.learning_areas.destroy');

// HR Settings: Deduction Categories & Types
Route::prefix('hr/settings/deductions')->name('hr.settings.deductions.')->group(function () {
    Route::get('/', [DeductionController::class, 'index'])->name('index');
    Route::post('/copy-schema', [DeductionController::class, 'copySchema'])->name('copySchema');
    Route::post('/toggle-manage-profile', [DeductionController::class, 'toggleManageProfile'])->name('toggleManageProfile');
    Route::post('/create-profile', [DeductionController::class, 'createProfile'])->name('createProfile');
    Route::post('/category', [DeductionController::class, 'storeCategory'])->name('category.store');
    Route::post('/category/{id}/update', [DeductionController::class, 'updateCategory'])->name('category.update');
    Route::post('/category/{id}/delete', [DeductionController::class, 'destroyCategory'])->name('category.destroy');
    Route::post('/category/{id}/toggle', [DeductionController::class, 'toggleCategory'])->name('category.toggle');
    Route::post('/type', [DeductionController::class, 'storeType'])->name('type.store');
    Route::post('/type/{id}/update', [DeductionController::class, 'updateType'])->name('type.update');
    Route::post('/type/{id}/toggle', [DeductionController::class, 'toggleType'])->name('type.toggle');
    Route::delete('/types/{id}', [DeductionController::class, 'destroyType'])->name('type.destroy');
});

// HR Settings: Income Types & Allowances
Route::prefix('hr/settings/incomes')->name('hr.settings.incomes.')->group(function () {
    Route::get('/', [IncomeTypeController::class, 'index'])->name('index');
    Route::post('/', [IncomeTypeController::class, 'store'])->name('store');
    Route::post('/{id}/update', [IncomeTypeController::class, 'update'])->name('update');
    Route::post('/{id}/toggle', [IncomeTypeController::class, 'toggle'])->name('toggle');
    Route::delete('/{id}', [IncomeTypeController::class, 'destroy'])->name('destroy');
});

// API: Income types for dynamic dropdown
Route::get('/api/income-types', [IncomeTypeController::class, 'apiList'])->name('api.income_types');

// Employee Routes (Service Record)
Route::get('/my-service-record', [EmployeeController::class, 'myServiceRecord'])->name('employee.service_record');
// Service Record Routes
Route::get('/my-service-record', [ServiceRecordController::class, 'index'])->name('service_record.index');
// Route::post('/my-service-record/store', [App\Http\Controllers\ServiceRecordController::class, 'store'])->name('service_record.store');
// Route::post('/my-service-record/delete/{id}', [App\Http\Controllers\ServiceRecordController::class, 'destroy'])->name('service_record.destroy');

// Dashboard Route pointing to our new controller
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Payroll Management Routes
Route::prefix('hr/payroll')->name('hr.payroll.')->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::post('/', [PayrollController::class, 'store'])->name('store'); // To generate a new period
    Route::put('/record/{id}', [PayrollController::class, 'updateRecord'])->name('update_record');
    Route::post('/{id}/approve', [PayrollController::class, 'approve'])->name('approve');
    Route::get('/{id}/export', [PayrollController::class, 'exportExcel'])->name('export');
    Route::post('/step-increment/{user}', [StepIncrementController::class, 'processIncrement'])->name('step_increment.process');
    Route::get('/employees', [PayrollController::class, 'employees'])->name('employees');
    Route::post('/toggle-deduction-version', [PayrollController::class, 'toggleDeductionVersion'])->name('toggle_deduction_version');
    Route::get('/{id}', [PayrollController::class, 'show'])->name('show');
});

// Loan Management & Amortization
Route::prefix('payroll/loans')->name('payroll.loans.')->group(function () {
    Route::get('/', [LoanController::class, 'index'])->name('index');
    Route::post('/', [LoanController::class, 'store'])->name('store');
    Route::put('/{loan}', [LoanController::class, 'update'])->name('update');
    Route::delete('/{loan}', [LoanController::class, 'destroy'])->name('destroy');
});

// Statutory Remittance Reporting (GSIS, PhilHealth, Pag-IBIG, BIR)
Route::prefix('payroll/remittances')->name('payroll.remittances.')->group(function () {
    Route::get('/', [RemittanceController::class, 'index'])->name('index');
    Route::get('/report', [RemittanceController::class, 'generateReport'])->name('report');
    Route::get('/export', [RemittanceController::class, 'exportCsv'])->name('export');
});

// Allowances & Other Incomes Management
Route::prefix('payroll/allowances')->name('payroll.allowances.')->group(function () {
    Route::get('/', [AllowanceController::class, 'index'])->name('index');
    Route::post('/{user}/update', [AllowanceController::class, 'update'])->name('update');
});

// Attendance & Lates Management
Route::prefix('payroll/attendance')->name('payroll.attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::post('/save', [AttendanceController::class, 'saveLates'])->name('save');
});

// Holiday & School Calendar
Route::prefix('payroll/holidays')->name('payroll.holidays.')->group(function () {
    Route::get('/', [HolidayController::class, 'index'])->name('index');
    Route::get('/api', [HolidayController::class, 'getCalendarEvents'])->name('events');
    Route::post('/', [HolidayController::class, 'store'])->name('store');
    Route::put('/{holiday}', [HolidayController::class, 'update'])->name('update');
    Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('destroy');
});

// BIR 2316 Year-End Certificate
Route::prefix('hr/bir-2316')->name('hr.bir2316.')->group(function () {
    Route::get('/', [Bir2316Controller::class, 'index'])->name('index');
    Route::get('/generate/{user_id}/{year}', [Bir2316Controller::class, 'generatePdf'])->name('generate');
});

// Employee Payroll (My Payslips)
Route::prefix('employee/payroll')->name('employee.payroll.')->group(function () {
    Route::get('/', [EmployeePayrollController::class, 'index'])->name('index');
    Route::get('/{id}', [EmployeePayrollController::class, 'show'])->name('show');
});

// Employee self-service: loans, attendance, tax documents
Route::prefix('employee/loans')->name('employee.loans.')->group(function () {
    Route::get('/', [EmployeeLoanController::class, 'index'])->name('index');
});

Route::prefix('employee/attendance')->name('employee.attendance.')->group(function () {
    Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
});

Route::prefix('employee/tax')->name('employee.tax.')->group(function () {
    Route::get('/', [EmployeeTaxController::class, 'index'])->name('index');
});

Route::get('/dev/cleanup-family-data', function () {
    $models = [PdsSpouse::class, PdsFather::class, PdsMother::class];

    foreach ($models as $model) {
        $duplicates = $model::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(id) > 1')
            ->pluck('user_id');

        foreach ($duplicates as $userId) {
            $records = $model::where('user_id', $userId)->orderBy('id', 'desc')->get();
            // Keep the first one (latest), delete the rest
            $records->shift();
            foreach ($records as $record) {
                $record->delete();
            }
        }
    }

    return 'Dummy family data cleaned successfully';
});

// Location API routes
Route::get('/api/locations/provinces/{region_code}', [PdsController::class, 'getProvinces'])->name('api.locations.provinces');
Route::get('/api/locations/cities/{province_code}', [PdsController::class, 'getCities'])->name('api.locations.cities');
Route::get('/api/locations/barangays/{city_code}', [PdsController::class, 'getBarangays'])->name('api.locations.barangays');
Route::get('/api/schools/search', [PdsController::class, 'searchSchools'])->name('api.schools.search');
