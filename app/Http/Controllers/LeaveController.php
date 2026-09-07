<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveCreditBalance;
use App\Models\LeaveCreditLog;
use App\Models\LeaveCreditSetting;
use App\Models\Seminar;
use App\Models\User;
use App\Services\LeaveCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use setasign\Fpdi\Tcpdf\Fpdi;

class LeaveController extends Controller
{
    /** role_id that identifies a plain employee (everyone else = HR / principal). */
    private const ROLE_EMPLOYEE = 1;

    /** Statuses recognised by the app. */
    private const STATUSES = ['PENDING', 'APPROVED', 'DISAPPROVED'];

    /** Template file, relative to storage/app. */
    private const TEMPLATE_PATH = 'templates/leave_template.pdf';

    /** Font size (pt) used for the "X" marks. Bump to 11-12 if the marks look small. */
    private const CHECK_FONT_SIZE = 10;

    /**
     * Fill section 7.B (recommendation) once a decision has been recorded.
     * Set to false if you want the printed form to stay blank for wet signatures.
     */
    private const RENDER_DECISION_SECTION = true;

    // =========================================================
    // GUARDS / SHARED LOOKUPS
    // =========================================================

    /**
     * Centralized auth guard so every method behaves the same way
     * instead of index() being the only one that checks the session.
     */
    private function requireAuth()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        return null;
    }

    /**
     * FIX: the old checks were `Session::get('role_id') == 1`. When role_id was
     * missing from the session that comparison is false, so a session with no
     * role at all was silently treated as management. Missing role now = employee.
     */
    private function isManagement(): bool
    {
        $roleId = Session::get('role_id');

        return $roleId !== null && (int) $roleId !== self::ROLE_EMPLOYEE;
    }

    private function leaveTypes(): array
    {
        return [
            'Vacation Leave',
            'Mandatory/Forced Leave',
            'Sick Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Special Privilege Leave',
            'Solo Parent Leave',
            'Study Leave',
            '10-Day VAWC Leave',
            'Rehabilitation Privilege',
            'Special Leave Benefits for Women',
            'Special Emergency (Calamity) Leave',
            'Adoption Leave',
            'Others',
        ];
    }

    /** Which 6.B details are valid for each 6.A type. (Moved out of store() so it can be reused.) */
    private function leaveDetailsMap(): array
    {
        return [
            'Vacation Leave' => ['Within the Philippines', 'Abroad', 'Monetization of Leave Credits', 'Terminal Leave'],
            'Mandatory/Forced Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Sick Leave' => ['In Hospital', 'Out Patient', 'Monetization of Leave Credits', 'Terminal Leave'],
            'Paternity Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Special Privilege Leave' => ['Within the Philippines', 'Abroad', 'Monetization of Leave Credits', 'Terminal Leave'],
            'Solo Parent Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Study Leave' => ["Completion of Master's Degree", 'BAR/Board Examination Review', 'Monetization of Leave Credits', 'Terminal Leave'],
            '10-Day VAWC Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Rehabilitation Privilege' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Special Leave Benefits for Women' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Special Emergency (Calamity) Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Adoption Leave' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Others' => ['Monetization of Leave Credits', 'Terminal Leave'],
            'Maternity Leave' => [], // no details allowed at all
        ];
    }

    /** 6.B details that actually have a "(Specify ...)" blank next to them on the form. */
    private function detailsWithSpecifyLine(): array
    {
        return ['Within the Philippines', 'Abroad', 'In Hospital', 'Out Patient'];
    }

    private function upper(?string $value): string
    {
        return mb_strtoupper(trim((string) $value), 'UTF-8');
    }

    private function formatDate(?string $value, string $format = 'F d, Y'): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value; // never let a bad date break the download
        }
    }

    // =========================================================
    // EMPLOYEE
    // =========================================================

    public function index()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = Session::get('user_id');

        $user = User::with('position')->find($user_id);

        if (! $user) {
            Session::forget('user_id');

            return redirect()->route('login')->with('error', 'Your session is no longer valid. Please log in again.');
        }

        // Fetch the employee's most recent Service Record
        $latest_service_record = DB::table('service_records')
            ->where('user_id', $user_id)
            ->orderBy('date_from', 'desc')
            ->first();

        // Extract designation (position) and salary, defaulting to empty if no record exists
        $current_position = $latest_service_record ? $latest_service_record->designation : '';
        $current_salary = $latest_service_record ? $latest_service_record->salary : '';

        $leaves = DB::table('leave_applications')
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Credit Ledger data
        $creditLogs = LeaveCreditLog::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $balanceRecord = $user->leaveCreditBalance;
        $currentBalance = $balanceRecord->vl_balance + $balanceRecord->sl_balance + $balanceRecord->service_credits;

        // My Seminars data
        $mySeminars = Seminar::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employee.leaves', compact(
            'user',
            'leaves',
            'current_position',
            'current_salary',
            'creditLogs',
            'currentBalance',
            'mySeminars',
        ));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = Session::get('user_id');

        $validated = $request->validate([
            'date_of_filing' => ['required', 'date'],
            'position' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'string', 'max:50'],
            'leave_type' => ['required', 'string', 'in:'.implode(',', $this->leaveTypes())],
            'leave_type_others' => ['nullable', 'required_if:leave_type,Others', 'string', 'max:255'],
            'leave_details' => ['nullable', 'string', 'max:255'],
            'leave_details_specific' => ['nullable', 'string', 'max:255'],
            'leave_details_specific' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'commutation' => ['required', 'in:Requested,Not Requested'],
        ], [
            'leave_type_others.required_if' => 'Please specify the leave type when selecting "Others".',
        ]);

        // Maternity Leave never carries a "details/reason" — force null regardless of what was submitted
        if ($validated['leave_type'] === 'Maternity Leave') {
            $validated['leave_details'] = null;
            $validated['leave_details_specific'] = null;
        }

        if (! empty($validated['leave_details'])) {
            $allowed = $this->leaveDetailsMap()[$validated['leave_type']] ?? [];
            if (! in_array($validated['leave_details'], $allowed, true)) {
                return back()->withInput()->withErrors([
                    'leave_details' => 'The selected detail does not apply to this leave type.',
                ]);
            }
        }

        // FIX: leave_details_specific used to be stored even for details that have no
        // "(Specify)" blank on the form (Monetization, Terminal Leave, Study Leave options).
        // The stray text was then dropped silently at print time. Clear it up front instead.
        // Special Leave Benefits for Women keeps its own "(Specify Illness)" line in 6.B.
        $hasSpecifyLine = in_array($validated['leave_details'] ?? '', $this->detailsWithSpecifyLine(), true)
            || $validated['leave_type'] === 'Special Leave Benefits for Women';

        if (! $hasSpecifyLine) {
            $validated['leave_details_specific'] = null;
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $workingDays = $startDate->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $endDate) + 1;

        $inclusiveDates = $startDate->format('M d, Y').' - '.$endDate->format('M d, Y');

        try {
            DB::table('leave_applications')->insert([
                'user_id' => $user_id,
                'office_department' => 'CNHS-JH', // hardcoded server-side, never trust client input for this
                'date_of_filing' => $validated['date_of_filing'],
                'position' => $this->upper($validated['position']),
                'salary' => $this->upper($validated['salary']),
                'leave_type' => $validated['leave_type'],
                'leave_type_others' => $this->upper($validated['leave_type_others'] ?? ''),
                'leave_details' => $validated['leave_details'] ?? null,
                'leave_details_specific' => $this->upper($validated['leave_details_specific'] ?? ''),
                'working_days' => $workingDays,
                'inclusive_dates' => $this->upper($inclusiveDates),
                'commutation' => $validated['commutation'],
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Leave application insert failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Something went wrong while submitting your application. Please try again.');
        }

        return back()->with('success', 'Leave application submitted successfully!');
    }

    public function destroy($id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = Session::get('user_id');

        $leave = DB::table('leave_applications')
            ->where('id', $id)
            ->where('user_id', $user_id)
            ->first();

        if (! $leave) {
            return back()->with('error', 'Leave application not found.');
        }

        if ($leave->status !== 'PENDING') {
            return back()->with('error', 'You can only cancel pending applications.');
        }

        try {
            // FIX: the delete now repeats the owner + status conditions, so an application
            // approved between the read above and this write can't be deleted anyway.
            $deleted = DB::table('leave_applications')
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->where('status', 'PENDING')
                ->delete();
        } catch (\Throwable $e) {
            Log::error('Leave application delete failed: '.$e->getMessage());

            return back()->with('error', 'Something went wrong while cancelling. Please try again.');
        }

        if ($deleted === 0) {
            return back()->with('error', 'This application can no longer be cancelled.');
        }

        return back()->with('success', 'Pending leave application cancelled.');
    }

    // =========================================================
    // HR & PRINCIPAL VIEW (MANAGEMENT)
    // =========================================================

    public function hrIndex(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = DB::table('leave_applications')
            ->join('users', 'leave_applications.user_id', '=', 'users.id')
            ->leftJoin('leave_credit_balances', 'users.id', '=', 'leave_credit_balances.user_id')
            ->select('leave_applications.*', 'users.first_name', 'users.last_name', 'leave_credit_balances.vl_balance', 'leave_credit_balances.sl_balance', 'leave_credit_balances.service_credits');

        // FIX: `$request->has('status')` was true even for `?status=`, and the requested
        // value went into the query unchecked. Whitelist it instead.
        $status = strtoupper(trim((string) $request->input('status', 'All')));
        if ($status !== '' && $status !== 'ALL' && in_array($status, self::STATUSES, true)) {
            $query->where('leave_applications.status', $status);
        }

        // Optional name search — harmless if your view has no search box.
        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->input('q'))).'%';
            $query->where(function ($q) use ($term) {
                $q->where('users.first_name', 'like', $term)
                    ->orWhere('users.last_name', 'like', $term);
            });
        }

        // FIX: ordering only ran in the "no filter" branch before, so a filtered list
        // came back in whatever order the DB felt like. CASE is portable (FIELD is MySQL-only).
        $leaves = $query
            ->orderByRaw("CASE leave_applications.status
                            WHEN 'PENDING' THEN 1
                            WHEN 'APPROVED' THEN 2
                            WHEN 'DISAPPROVED' THEN 3
                            ELSE 4 END")
            ->orderBy('leave_applications.created_at', 'desc')
            ->get();

        // FIX: stats were counted from the already-filtered collection, so filtering by
        // "Approved" reported 0 pending / 0 denied. Count from the whole table instead.
        $statusCounts = DB::table('leave_applications')
            ->join('users', 'leave_applications.user_id', '=', 'users.id')
            ->select('leave_applications.status', DB::raw('COUNT(*) as total'))
            ->groupBy('leave_applications.status')
            ->pluck('total', 'status');

        $stats = [
            'pending' => (int) ($statusCounts['PENDING'] ?? 0),
            'approved' => (int) ($statusCounts['APPROVED'] ?? 0),
            'denied' => (int) ($statusCounts['DISAPPROVED'] ?? 0),
        ];

        // Employee Balances tab
        $employees = User::with(['position', 'leaveCreditBalance'])
            ->where('role_id', self::ROLE_EMPLOYEE)
            ->get();

        // Seminar Approvals tab
        $pendingSeminars = Seminar::with('user')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->get();
        $pendingSeminarsCount = $pendingSeminars->count();

        // Configure Rates modal
        $settingsRaw = LeaveCreditSetting::all();
        $settings = [];
        foreach ($settingsRaw as $row) {
            $key = $row->setting_key;
            if ($key === 'seminar_hour_to_credit_rate') {
                $key = 'seminar_rate';
            }
            $settings[$row->employee_type][$key] = $row->setting_value;
        }

        return view('hr.leaves.index', compact(
            'leaves',
            'stats',
            'employees',
            'pendingSeminars',
            'pendingSeminarsCount',
            'settings',
        ));
    }

    public function hrUpdateStatus(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:APPROVED,DISAPPROVED',
            // max:255 keeps the write inside a VARCHAR(255) column; raise it if hr_remarks is TEXT.
            'hr_remarks' => 'nullable|string|max:255',
        ]);

        // FIX: the old code updated blindly. A bad id "succeeded" and still showed
        // "Leave application approved." Check the row exists first.
        $leave = DB::table('leave_applications')->where('id', $id)->first();

        if (! $leave) {
            return back()->with('error', 'Leave application not found.');
        }

        try {
            DB::transaction(function () use ($id, $request, $leave) {
                $daysWithPay = null;
                $daysWithoutPay = null;

                if ($request->status === 'APPROVED' && $leave->status !== 'APPROVED') {
                    $leaveModel = LeaveApplication::find($id);
                    if ($leaveModel) {
                        if (in_array($leaveModel->leave_type, ['Maternity Leave', 'Paternity Leave'])) {
                            $daysWithPay = (float) $leaveModel->working_days;
                            $daysWithoutPay = 0;
                        } else {
                            $balanceRecord = LeaveCreditBalance::firstOrCreate(
                                ['user_id' => $leaveModel->user_id],
                                ['vl_balance' => 0, 'sl_balance' => 0, 'service_credits' => 0]
                            );

                            $bucketColumn = ($leaveModel->leave_type === 'Sick Leave') ? 'sl_balance' : 'vl_balance';
                            $available = $balanceRecord->{$bucketColumn};
                            $workingDays = (float) $leaveModel->working_days;

                            $daysWithPay = min($workingDays, $available);
                            $daysWithoutPay = max(0, $workingDays - $available);
                        }

                        app(LeaveCreditService::class)->processLeaveDeduction($leaveModel, (float) $leaveModel->working_days);
                    }
                } elseif ($request->status === 'DISAPPROVED' && $leave->status === 'APPROVED') {
                    $leaveModel = LeaveApplication::find($id);
                    if ($leaveModel) {
                        app(LeaveCreditService::class)->processLeaveRefund($leaveModel);
                    }
                }

                $updateData = [
                    'status' => $request->status,
                    'hr_remarks' => $this->upper($request->input('hr_remarks', '')),
                    'updated_at' => now(),
                ];

                if ($request->status === 'APPROVED' && $leave->status !== 'APPROVED') {
                    $updateData['days_with_pay'] = $daysWithPay;
                    $updateData['days_without_pay'] = $daysWithoutPay;
                } elseif ($request->status !== 'APPROVED') {
                    $updateData['days_with_pay'] = null;
                    $updateData['days_without_pay'] = null;
                }

                DB::table('leave_applications')->where('id', $id)->update($updateData);
            });
        } catch (\Throwable $e) {
            Log::error('Leave status update failed: '.$e->getMessage());

            return back()->with('error', 'Something went wrong while updating this application. Please try again.');
        }

        $message = $request->status === 'APPROVED' ? 'Leave application approved.' : 'Leave application disapproved.';

        return back()->with('success', $message);
    }

    // =========================================================
    // PDF EXPORT
    // =========================================================

    /**
     * Field geometry, in millimetres, measured directly from leave_template.pdf
     * (A4, 210 x 297 mm, origin at the top-left corner).
     *
     * Text fields are [x, y, width, height] boxes — text is centred vertically in the
     * box and shrunk horizontally if it would overflow, so long names/dates can't spill
     * into the next field.
     *
     * Checkboxes are [x, y] of the printed box's top-left corner; every box on this
     * form is 2.3 x 2.3 mm (self::CHECK_BOX_SIZE).
     */
    private const CHECK_BOX_SIZE = 2.3;

    private function pdfLayout(): array
    {
        return [
            // 1-5 header
            'text' => [
                'office' => [19.35, 50.00, 51.10, 6.00],

                // Name is split into three cells, each centred under its own printed
                // (Last) (First) (Middle) header instead of one run-on string.
                'name_last' => [90.70, 50.40, 29.00, 4.50],
                'name_first' => [123.20, 50.40, 29.00, 4.50],
                'name_middle' => [152.40, 50.40, 29.00, 4.50],
                // x starts just after each printed label, width stops at the end of the
                // rule, y seats the baseline on the rule instead of floating above it.
                'date_of_filing' => [43.20, 59.20, 27.00, 4.50],
                'position' => [98.60, 59.20, 46.00, 4.50],
                'salary' => [166.30, 59.20, 23.50, 4.40],

                // 6.A "Others:" blank
                'others' => [16.50, 158.90, 63.50, 4.40],

                // 6.C
                'working_days' => [19.85, 171.30, 64.44, 4.50],
                'inclusive_dates' => [19.85, 181.90, 64.44, 4.60],
            ],

            // 6.B "(Specify ...)" blanks — one per detail, keyed the same as leave_details.
            // FIX: "Within the Philippines" used to print on the Abroad line.
            'specify' => [
                'Within the Philippines' => [151.00, 84.90, 43.30, 4.50],
                'Abroad' => [145.00, 90.20, 49.30, 4.50],
                'In Hospital' => [158.10, 100.80, 35.80, 4.40],
                'Out Patient' => [158.90, 106.00, 35.00, 4.50],
                // Not a checkbox — belongs to the 6.A type, printed whenever that type is chosen.
                'Special Leave Benefits for Women' => [138.60, 122.00, 54.50, 4.50],
            ],

            // 6.A checkboxes (left column, x = 15.5)
            'leave_types' => [
                'Vacation Leave' => [15.50, 80.50],
                'Mandatory/Forced Leave' => [15.20, 86.00],
                'Sick Leave' => [15.50, 91.30],
                'Maternity Leave' => [15.50, 96.60],
                'Paternity Leave' => [15.50, 101.60],
                'Special Privilege Leave' => [15.50, 106.90],
                'Solo Parent Leave' => [15.50, 112.50],
                'Study Leave' => [15.50, 117.70],
                '10-Day VAWC Leave' => [15.50, 123.10],
                'Rehabilitation Privilege' => [15.50, 128.40],
                'Special Leave Benefits for Women' => [15.50, 133.60],
                'Special Emergency (Calamity) Leave' => [15.50, 138.90],
                'Adoption Leave' => [15.50, 144.20],
                // "Others" has no checkbox on this form — only the blank line above.
            ],

            // 6.B checkboxes (right column, x = 116.4)
            'details' => [
                'Within the Philippines' => [116.40, 86.00],
                'Abroad' => [116.40, 91.30],
                'In Hospital' => [116.40, 101.60],
                'Out Patient' => [116.40, 106.90],
                "Completion of Master's Degree" => [116.40, 138.90],
                'BAR/Board Examination Review' => [116.40, 144.40],
                'Monetization of Leave Credits' => [116.40, 155.00],
                'Terminal Leave' => [116.40, 160.10],
            ],

            // 6.D
            'commutation' => [
                'Not Requested' => [116.40, 172.30],
                'Requested' => [116.40, 177.70],
            ],

            // 7.A CERTIFICATION OF LEAVE CREDITS
            // Pushed X right by 4mm (48 -> 52) to give spacing after the word "As"
            'as_of_date' => [52.00, 205.00, 55.00, 4.50],

            // The table balances are perfect; do not change these!
            'balances' => [
                'vacation_earned' => [55.00, 214.00, 25.00, 4.50],
                'vacation_deducted' => [55.00, 218.00, 25.00, 4.50],
                'vacation_balance' => [55.00, 222.00, 25.00, 4.50],

                'sick_earned' => [85.00, 214.00, 25.00, 4.50],
                'sick_deducted' => [85.00, 218.00, 25.00, 4.50],
                'sick_balance' => [85.00, 222.00, 25.00, 4.50],
            ],

            // 7.B — filled only once a decision exists (see RENDER_DECISION_SECTION)
            'recommendation' => [
                'APPROVED' => [116.40, 205.40],
                'DISAPPROVED' => [116.40, 210.90],
            ],
            'remark_lines' => [
                [152.50, 208.80, 40.20, 4.50],
                [121.30, 213.60, 72.50, 4.20],
                [121.50, 217.60, 72.50, 4.20],
                [121.70, 221.30, 72.50, 4.20],
            ],

            // 7.C Approved For
            'approved_days_with_pay' => [20.00, 245.00, 20.00, 4.50],
            'approved_days_without_pay' => [20.00, 250.00, 20.00, 4.50],
            'approved_others' => [20.00, 255.00, 20.00, 4.50],

            // 7.D Disapproved Due To
            'disapproved_lines' => [
                [120.00, 245.00, 75.00, 4.50],
                [120.00, 250.00, 75.00, 4.50],
                [120.00, 255.00, 75.00, 4.50],
            ],
        ];
    }

    /**
     * @param  Request  $request  injected by Laravel; the {id} route parameter still
     *                            binds to $id, so existing routes keep working.
     */
    public function exportLeavePDF(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $leave = DB::table('leave_applications')
            ->join('users', 'leave_applications.user_id', '=', 'users.id')
            ->select('leave_applications.*', 'users.first_name', 'users.last_name', 'users.middle_name', 'users.id as u_id')
            ->where('leave_applications.id', $id)
            ->first();

        if (! $leave) {
            return back()->with('error', 'Leave application not found.');
        }

        // FIX: previously ANY logged-in user could download ANY application just by
        // changing the id in the URL. Employees are now limited to their own records.
        if (! $this->isManagement() && (int) $leave->user_id !== (int) Session::get('user_id')) {
            return back()->with('error', 'You are not allowed to download this leave application.');
        }

        $templatePath = storage_path('app/'.self::TEMPLATE_PATH);

        if (! is_readable($templatePath)) {
            Log::error('Leave PDF template missing or unreadable: '.$templatePath);

            return back()->with('error', 'PDF Template file not found.');
        }

        // ?debug=1 draws the field boxes in red so coordinates can be checked visually.
        $debug = config('app.debug') && $request->boolean('debug');

        try {
            $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetCellPadding(0);
            // FIX: without this, a Write() near the bottom of the sheet can push TCPDF
            // into adding a blank second page.
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetTitle('Application for Leave');
            $pdf->SetCreator('CNHS-JH Leave System');

            // FIX: AddPage() used to run BEFORE setSourceFile()/importPage(). FPDI needs the
            // page imported first so the sheet can be created at the template's real size.
            $pdf->setSourceFile($templatePath);
            $tplIdx = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($tplIdx);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx, 0, 0, $size['width'], $size['height']);

            $pdf->SetTextColor(0, 0, 0);

            $layout = $this->pdfLayout();

            // ---- helpers -------------------------------------------------
            $box = function (array $b) use ($pdf, $debug) {
                if ($debug) {
                    $pdf->SetDrawColor(220, 0, 0);
                    $pdf->SetLineWidth(0.1);
                    $pdf->Rect($b[0], $b[1], $b[2], $b[3]);
                }
            };

            // Writes text inside a box: vertically centred, shrunk to fit if too wide.
            $writeBox = function (array $b, ?string $text, string $align = 'L', float $fontSize = 9) use ($pdf, $box) {
                $box($b);
                $text = trim((string) $text);
                if ($text === '') {
                    return;
                }
                $pdf->SetFont('helvetica', '', $fontSize);
                $pdf->SetXY($b[0], $b[1]);
                // stretch = 1 → scale horizontally only when the text would overflow
                $pdf->Cell($b[2], $b[3], $text, 0, 0, $align, false, '', 1, true, 'T', 'M');
            };

            // Draws a centred "X" inside a 2.3 mm checkbox.
            $drawCheck = function (array $p) use ($pdf, $box) {
                $s = self::CHECK_BOX_SIZE;
                $box([$p[0], $p[1], $s, $s]);
                $pdf->SetFont('helvetica', 'B', self::CHECK_FONT_SIZE);
                $pdf->SetXY($p[0], $p[1]);
                $pdf->Cell($s, $s, 'X', 0, 0, 'C', false, '', 0, true, 'T', 'M');
                $pdf->SetFont('helvetica', '', 9);
            };

            // Wraps text across a set of pre-printed blank lines.
            $writeOnLines = function (?string $text, array $lines, float $fontSize = 8) use ($pdf, $writeBox) {
                $text = trim((string) $text);
                if ($text === '' || empty($lines)) {
                    return;
                }
                $pdf->SetFont('helvetica', '', $fontSize);
                $words = preg_split('/\s+/', $text) ?: [];
                $rows = [];
                $current = '';
                foreach ($words as $word) {
                    if (count($rows) >= count($lines)) {
                        break;
                    }
                    $lineWidth = $lines[count($rows)][2];
                    $candidate = $current === '' ? $word : $current.' '.$word;
                    if ($current !== '' && $pdf->GetStringWidth($candidate) > $lineWidth) {
                        $rows[] = $current;
                        $current = $word;
                    } else {
                        $current = $candidate;
                    }
                }
                if ($current !== '' && count($rows) < count($lines)) {
                    $rows[] = $current;
                }
                foreach ($rows as $i => $row) {
                    $writeBox($lines[$i], $row, 'L', $fontSize);
                }
            };
            // --------------------------------------------------------------

            // 1-5 header
            $writeBox($layout['text']['office'], $this->upper($leave->office_department));
            $writeBox($layout['text']['name_last'], $this->upper($leave->last_name), 'C');
            $writeBox($layout['text']['name_first'], $this->upper($leave->first_name), 'C');
            $writeBox($layout['text']['name_middle'], $this->upper($leave->middle_name ?? ''), 'C');
            $writeBox($layout['text']['date_of_filing'], $this->upper($this->formatDate($leave->date_of_filing)));

            // Get position from user profile
            $employee = User::with('position')->find($leave->u_id);
            $profilePosition = $employee && $employee->position ? ($employee->position->name ?? $employee->position->position_name ?? 'No Position Assigned') : 'No Position Assigned';

            $writeBox($layout['text']['position'], $this->upper($profilePosition));
            $writeBox($layout['text']['salary'], $this->upper($leave->salary), 'C');

            // 6.A type of leave — exactly one mark
            $leaveTypeKey = trim((string) $leave->leave_type);
            if (isset($layout['leave_types'][$leaveTypeKey])) {
                $drawCheck($layout['leave_types'][$leaveTypeKey]);
            }

            // 6.A "Others" is a blank line, not a checkbox
            if ($leaveTypeKey === 'Others' && trim((string) $leave->leave_type_others) !== '') {
                $writeBox($layout['text']['others'], $this->upper($leave->leave_type_others));
            }

            // 6.B details of leave
            $leaveDetailsKey = trim((string) ($leave->leave_details ?? ''));
            if (isset($layout['details'][$leaveDetailsKey])) {
                $drawCheck($layout['details'][$leaveDetailsKey]);
            }

            // 6.B "(Specify ...)" text — each detail now writes on its own blank line.
            $specific = trim((string) ($leave->leave_details_specific ?? ''));
            if ($specific !== '') {
                // Special Leave Benefits for Women has a specify line tied to the 6.A type,
                // not to a 6.B checkbox — this was never printed before.
                $specifyKey = $leaveTypeKey === 'Special Leave Benefits for Women'
                    ? 'Special Leave Benefits for Women'
                    : $leaveDetailsKey;

                if (isset($layout['specify'][$specifyKey])) {
                    $writeBox($layout['specify'][$specifyKey], $this->upper($specific), 'L', 8);
                }
            }

            // 6.C
            $writeBox($layout['text']['working_days'], $leave->working_days.' DAY'.((int) $leave->working_days === 1 ? '' : 'S'), 'C');
            $writeBox($layout['text']['inclusive_dates'], $this->upper($leave->inclusive_dates), 'C');

            // 6.D commutation
            $commutationKey = trim((string) ($leave->commutation ?? ''));
            if (isset($layout['commutation'][$commutationKey])) {
                $drawCheck($layout['commutation'][$commutationKey]);
            }

            // 7.A CERTIFICATION OF LEAVE CREDITS
            $status = strtoupper(trim((string) ($leave->status ?? '')));
            $isActioned = in_array($status, ['APPROVED', 'RECOMMENDED'], true);

            // Determine "As of" date
            $asOfDate = $isActioned
                ? Carbon::parse($leave->updated_at)
                : Carbon::now();
            $writeBox($layout['as_of_date'], $asOfDate->format('M d, Y'), 'L', 8);

            // Fetch the deduction log tied to this specific leave application
            $deductionLog = LeaveCreditLog::where('source', 'leave_deduction')
                ->where('reference_id', $leave->id)
                ->first();

            if ($deductionLog) {
                // --- Leave is Approved: calculate historical balances ---
                $deductedBucket = $deductionLog->leave_bucket; // 'VL' or 'SL'
                $lessThisApp = abs($deductionLog->amount);
                $balanceAfter = $deductionLog->balance_after;
                $totalEarned = $balanceAfter + $lessThisApp;

                if ($deductedBucket === 'VL') {
                    $writeBox($layout['balances']['vacation_earned'], number_format($totalEarned, 3), 'C');
                    $writeBox($layout['balances']['vacation_deducted'], number_format($lessThisApp, 3), 'C');
                    $writeBox($layout['balances']['vacation_balance'], number_format($balanceAfter, 3), 'C');

                    // SL: fetch the most recent log at or before the 'As of' date
                    $otherLog = LeaveCreditLog::where('user_id', $leave->u_id)
                        ->where('leave_bucket', 'SL')
                        ->where('created_at', '<=', $asOfDate)
                        ->orderByDesc('created_at')
                        ->first();
                    $otherBalance = $otherLog ? $otherLog->balance_after : 0;
                    $writeBox($layout['balances']['sick_earned'], number_format($otherBalance, 3), 'C');
                    $writeBox($layout['balances']['sick_balance'], number_format($otherBalance, 3), 'C');
                } else {
                    // SL was deducted
                    $writeBox($layout['balances']['sick_earned'], number_format($totalEarned, 3), 'C');
                    $writeBox($layout['balances']['sick_deducted'], number_format($lessThisApp, 3), 'C');
                    $writeBox($layout['balances']['sick_balance'], number_format($balanceAfter, 3), 'C');

                    // VL: fetch the most recent log at or before the 'As of' date
                    $otherLog = LeaveCreditLog::where('user_id', $leave->u_id)
                        ->where('leave_bucket', 'VL')
                        ->where('created_at', '<=', $asOfDate)
                        ->orderByDesc('created_at')
                        ->first();
                    $otherBalance = $otherLog ? $otherLog->balance_after : 0;
                    $writeBox($layout['balances']['vacation_earned'], number_format($otherBalance, 3), 'C');
                    $writeBox($layout['balances']['vacation_balance'], number_format($otherBalance, 3), 'C');
                }
            } else {
                // --- Leave is Pending: show live balances ---
                $balance = LeaveCreditBalance::where('user_id', $leave->u_id)->first();
                if ($balance) {
                    $writeBox($layout['balances']['vacation_earned'], number_format($balance->vl_balance, 3), 'C');
                    $writeBox($layout['balances']['vacation_balance'], number_format($balance->vl_balance, 3), 'C');
                    $writeBox($layout['balances']['sick_earned'], number_format($balance->sl_balance, 3), 'C');
                    $writeBox($layout['balances']['sick_balance'], number_format($balance->sl_balance, 3), 'C');
                }
            }

            // 7.B recommendation — only once HR/principal has acted
            if (self::RENDER_DECISION_SECTION && isset($layout['recommendation'][$status])) {
                $drawCheck($layout['recommendation'][$status]);
            }

            // 7.C / 7.D
            if ($status === 'APPROVED') {
                if ($leave->days_with_pay > 0) {
                    $formattedWithPay = (string) (float) $leave->days_with_pay;
                    $writeBox($layout['approved_days_with_pay'], $formattedWithPay, 'C');
                }
                if ($leave->days_without_pay > 0) {
                    $formattedWithoutPay = (string) (float) $leave->days_without_pay;
                    $writeBox($layout['approved_days_without_pay'], $formattedWithoutPay, 'C');
                }

            } elseif ($status === 'DISAPPROVED') {
                $writeOnLines($this->upper($leave->hr_remarks ?? ''), $layout['disapproved_lines']);
            }

            $lastName = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $leave->last_name);
            $fileName = 'Leave_Application_'.$leave->id.($lastName !== '' ? '_'.$lastName : '').'.pdf';

            // FIX: the old code called Output(..., 'D') then exit;, which bypasses Laravel's
            // response pipeline (middleware, session writes, terminable handlers).
            $content = $pdf->Output($fileName, 'S');
        } catch (\Throwable $e) {
            Log::error('Leave PDF export failed for id '.$id.': '.$e->getMessage());

            return back()->with('error', 'Something went wrong while generating the PDF. Please try again.');
        }

        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$fileName.'"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
