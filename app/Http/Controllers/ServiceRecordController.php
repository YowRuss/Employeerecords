<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ServiceRecordController extends Controller
{
    // =========================================================
    // EMPLOYEE VIEW (READ-ONLY)
    // =========================================================
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user_id = Session::get('user_id');

        $user = DB::table('users')->where('id', $user_id)->first();
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $records = DB::table('service_records')->where('user_id', $user_id)->orderBy('date_from', 'asc')->get();

        return view('employee.service_record', compact('user', 'personal_info', 'records'));
    }

    // =========================================================
    // HR VIEW & MANAGEMENT
    // =========================================================
    public function hrIndex($user_id)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user = DB::table('users')->where('id', $user_id)->first();
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $records = DB::table('service_records')->where('user_id', $user_id)->orderBy('date_from', 'asc')->get();

        // ADD THIS: Fetch all available positions from the database
        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();

        // Pass $positions to the view
        return view('hr.service_records.show', compact('user', 'personal_info', 'records', 'positions'));
    }

    public function hrStore(Request $request, $user_id)
    {
        DB::table('service_records')->insert([
            'user_id' => $user_id, // Attached to the specific employee
            'date_from' => $request->date_from,
            'date_to' => strtoupper($request->date_to),
            'designation' => strtoupper($request->designation),
            'status' => strtoupper($request->status),
            'salary' => strtoupper($request->salary),
            'station_place' => strtoupper($request->station_place),
            'branch' => strtoupper($request->branch),
            'leave_without_pay' => strtoupper($request->leave_without_pay ?? 'NONE'),
            'separation_date' => strtoupper($request->separation_date),
            'separation_cause' => strtoupper($request->separation_cause ?? 'NONE'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Service record entry added successfully!');
    }

    public function hrDestroy($id)
    {
        DB::table('service_records')->where('id', $id)->delete();

        return back()->with('success', 'Service record deleted.');
    }

    // =========================================================
    // HR SERVICE RECORD DIRECTORY
    // =========================================================
    public function hrDirectory(Request $request)
    {
        if (! Session::has('user_id') || Session::get('role_id') == 1) { // Block standard employees
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $filter = $request->query('filter', 'all');

        $query = User::with('position')->where('role_id', 1);

        if ($filter === 'teaching') {
            $query->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::Teaching->value);
            });
        } elseif ($filter === 'non-teaching') {
            $query->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::NonTeaching->value);
            });
        }

        $employees = $query->orderBy('last_name', 'asc')->paginate(10)->withQueryString();

        $allCount = User::where('role_id', 1)->count();
        $teachingCount = User::where('role_id', 1)
            ->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::Teaching->value);
            })->count();
        $nonTeachingCount = User::where('role_id', 1)
            ->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::NonTeaching->value);
            })->count();

        return view('hr.service_records.index', compact('employees', 'filter', 'allCount', 'teachingCount', 'nonTeachingCount'));
    }
}
