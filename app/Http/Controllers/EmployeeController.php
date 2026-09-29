<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EmployeeController extends Controller
{
    /**
     * Display the Employee Dashboard
     */
    public function dashboard()
    {
        // Make sure the user is actually logged in
        if (! Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $userId = Session::get('user_id');

        $announcements = Announcement::latest()->take(3)->get();
        $events = Event::where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();

        // You can fetch recent leaves or notifications here to display on their dashboard
        $recentLeaves = DB::table('leaves')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('announcements', 'events', 'recentLeaves'));
    }

    /**
     * Display the Employee's own Service Record (Read-Only)
     */
    public function myServiceRecord()
    {
        // Make sure the user is actually logged in
        if (! Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $userId = Session::get('user_id');

        // Fetch user details and PDS (for the birth date/place in the header)
        $employee = DB::table('users')->where('id', $userId)->first();
        $user = $employee;
        $personal_info = DB::table('pds_personal_info')->where('user_id', $userId)->first();
        $pds = $personal_info;

        // Fetch only THEIR service records, sorted chronologically by start date
        $records = DB::table('service_records')
            ->where('user_id', $userId)
            ->orderBy('date_from', 'asc')
            ->get();
        $serviceRecords = $records;

        // Send all this data to the Blade view
        return view('employee.service_record', compact('employee', 'user', 'pds', 'personal_info', 'records', 'serviceRecords'));
    }

    /**
     * Fetch paginated employees as JSON for modal selection.
     */
    public function apiGetEmployees(Request $request): JsonResponse
    {
        try {
            $category = strtolower($request->query('category', 'teaching'));
            $search = trim($request->query('search', ''));

            // Map the frontend string to the correct Enum value
            $enumValue = ($category === 'non-teaching')
                ? PositionCategory::NonTeaching
                : PositionCategory::Teaching;

            $query = User::with(['position', 'learningArea'])
                ->where('role_id', 1)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Inactive');
                });

            $query->whereHas('position', function ($q) use ($enumValue) {
                $q->where('category', $enumValue->value);
            });

            if ($search !== '') {
                $term = strtolower($search);
                $query->where(function ($q) use ($term) {
                    $q->where(DB::raw('LOWER(first_name)'), 'like', '%'.$term.'%')
                        ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%'.$term.'%')
                        ->orWhere(DB::raw('LOWER(middle_name)'), 'like', '%'.$term.'%')
                        ->orWhere(DB::raw("CONCAT(LOWER(first_name), ' ', LOWER(last_name))"), 'like', '%'.$term.'%')
                        ->orWhere(DB::raw("CONCAT(LOWER(last_name), ' ', LOWER(first_name))"), 'like', '%'.$term.'%')
                        ->orWhereHas('position', function ($qPos) use ($term) {
                            $qPos->where(DB::raw('LOWER(position_name)'), 'like', '%'.$term.'%');
                        });
                });
            }

            $employees = $query->orderBy('last_name', 'asc')->paginate(10);

            return response()->json($employees, 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
