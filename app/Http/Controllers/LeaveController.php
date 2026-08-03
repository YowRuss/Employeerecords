<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LeaveController extends Controller
{
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

    public function index()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = Session::get('user_id');

        $user = \App\Models\User::with('position')->find($user_id);

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

        // Pass $current_position and $current_salary to the view
        return view('employee.leaves', compact('user', 'leaves', 'current_position', 'current_salary'));
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
            'working_days' => ['required', 'integer', 'min:1', 'max:365'],
            'inclusive_dates' => ['required', 'string', 'max:255'],
            'commutation' => ['required', 'in:Requested,Not Requested'],
        ], [
            'leave_type_others.required_if' => 'Please specify the leave type when selecting "Others".',
        ]);

        // Maternity Leave never carries a "details/reason" — force null regardless of what was submitted
        if ($validated['leave_type'] === 'Maternity Leave') {
            $validated['leave_details'] = null;
            $validated['leave_details_specific'] = null;
        }

        $leaveDetailsMap = [
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

        if (! empty($validated['leave_details'])) {
            $allowed = $leaveDetailsMap[$validated['leave_type']] ?? [];
            if (! in_array($validated['leave_details'], $allowed, true)) {
                return back()->withInput()->withErrors([
                    'leave_details' => 'The selected detail does not apply to this leave type.',
                ]);
            }
        }

        try {
            DB::table('leave_applications')->insert([
                'user_id' => $user_id,
                'office_department' => 'CNHS-JH', // hardcoded server-side, never trust client input for this
                'date_of_filing' => $validated['date_of_filing'],
                'position' => strtoupper($validated['position']),
                'salary' => strtoupper($validated['salary']),
                'leave_type' => $validated['leave_type'],
                'leave_type_others' => strtoupper($validated['leave_type_others'] ?? ''),
                'leave_details' => $validated['leave_details'] ?? null,
                'leave_details_specific' => strtoupper($validated['leave_details_specific'] ?? ''),
                'working_days' => $validated['working_days'],
                'inclusive_dates' => strtoupper($validated['inclusive_dates']),
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
            DB::table('leave_applications')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            Log::error('Leave application delete failed: '.$e->getMessage());

            return back()->with('error', 'Something went wrong while cancelling. Please try again.');
        }

        return back()->with('success', 'Pending leave application cancelled.');
    }

    // =========================================================
    // HR & PRINCIPAL VIEW (MANAGEMENT)
    // =========================================================

    public function hrIndex(Request $request)
    {
        if (! Session::has('user_id') || Session::get('role_id') == 1) { // Assuming role_id 1 is Employee
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = DB::table('leave_applications')
            ->join('users', 'leave_applications.user_id', '=', 'users.id')
            ->select('leave_applications.*', 'users.first_name', 'users.last_name');

        if ($request->has('status') && $request->status != 'All') {
            $query->where('leave_applications.status', strtoupper($request->status));
        } else {
            $query->orderByRaw("FIELD(leave_applications.status, 'PENDING', 'APPROVED', 'DISAPPROVED')")
                ->orderBy('leave_applications.created_at', 'desc');
        }

        $leaves = $query->get();

        $stats = [
            'pending' => $leaves->where('status', 'PENDING')->count(),
            'approved' => $leaves->where('status', 'APPROVED')->count(),
            'denied' => $leaves->where('status', 'DISAPPROVED')->count(),
        ];

        return view('hr.leaves.index', compact('leaves', 'stats'));
    }

    public function hrUpdateStatus(Request $request, $id)
    {
        if (! Session::has('user_id') || Session::get('role_id') == 1) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:APPROVED,DISAPPROVED',
            'hr_remarks' => 'nullable|string',
        ]);

        DB::table('leave_applications')->where('id', $id)->update([
            'status' => $request->status,
            'hr_remarks' => strtoupper($request->hr_remarks),
            'updated_at' => now(),
        ]);

        $message = $request->status == 'APPROVED' ? 'Leave application approved.' : 'Leave application disapproved.';

        return back()->with('success', $message);
    }
}
