<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PrincipalReportController extends Controller
{
    public function index()
    {
        // Strictly limit to Principal (Role 4)
        if (Session::get('role_id') != 4) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // 1. Employee Statistics
        $totalEmployees = DB::table('users')->where('role_id', 1)->count();
        $totalHR = DB::table('users')->where('role_id', 2)->count();

        // Employees by Position (if positions table is linked)
        $employeesByPosition = DB::table('users')
            ->join('positions', 'users.id', '=', 'positions.id') // Adjust join condition if your foreign key is different
            ->select('positions.position_name', DB::raw('count(users.id) as total'))
            ->where('users.role_id', 1)
            ->groupBy('positions.position_name')
            ->get();

        // 2. Leave Statistics
        $leaveStats = DB::table('leave_applications')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalLeaves = array_sum($leaveStats);

        // 3. Event Statistics
        $totalEvents = DB::table('events')->count();
        $totalRegistrations = DB::table('event_attendees')->count();
        $totalAttended = DB::table('event_attendees')->where('status', 'Attended')->count();

        // 4. Announcement Statistics
        $totalAnnouncements = DB::table('announcements')->count();
        $totalAcknowledgments = DB::table('announcement_acknowledgments')->count();

        return view('principal.reports.index', compact(
            'totalEmployees',
            'totalHR',
            'employeesByPosition',
            'leaveStats',
            'totalLeaves',
            'totalEvents',
            'totalRegistrations',
            'totalAttended',
            'totalAnnouncements',
            'totalAcknowledgments'
        ));
    }
}
