<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HrReportController extends Controller
{
    public function index()
    {
        // Strictly limit to HR Admin (Role 2)
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // 1. Employee Statistics
        $totalEmployees = DB::table('users')->where('role_id', 1)->count();

        $employeesByPosition = DB::table('users')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->where('users.role_id', 1)
            ->selectRaw("COALESCE(positions.position_name, 'Unassigned') as position_name, count(users.id) as total")
            ->groupByRaw("COALESCE(positions.position_name, 'Unassigned')")
            ->orderByDesc('total')
            ->orderBy('position_name')
            ->get();

        // 2. Leave Statistics
        $leaveCounts = DB::table('leave_applications')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $leaveStats = ['Pending' => 0, 'Approved' => 0, 'Denied' => 0];

        foreach ($leaveCounts as $status => $total) {
            $key = strtoupper((string) $status);

            if ($key === 'PENDING') {
                $leaveStats['Pending'] += $total;
            } elseif ($key === 'APPROVED') {
                $leaveStats['Approved'] += $total;
            } elseif (in_array($key, ['DISAPPROVED', 'DENIED', 'REJECTED'], true)) {
                $leaveStats['Denied'] += $total;
            }
        }

        // 3. Event Statistics
        $totalEvents = DB::table('events')->count();
        $totalRegistrations = DB::table('event_attendees')->count();
        $totalAttended = DB::table('event_attendees')->where('status', 'Attended')->count();

        // 4. Announcement Statistics
        $totalAnnouncements = DB::table('announcements')->count();
        $totalAcknowledgments = DB::table('announcement_acknowledgments')->count();

        return view('hr.reports.index', compact(
            'totalEmployees',
            'employeesByPosition',
            'leaveStats',
            'totalEvents',
            'totalRegistrations',
            'totalAttended',
            'totalAnnouncements',
            'totalAcknowledgments'
        ));
    }
}
