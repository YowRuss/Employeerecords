<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PrincipalLeaveController extends Controller
{
    public function index(Request $request)
    {
        // Strictly limit to Principal (Role 4)
        if (Session::get('role_id') != 4) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $query = DB::table('leave_applications')
            ->join('users', 'leave_applications.user_id', '=', 'users.id')
            ->select('leave_applications.*', 'users.first_name', 'users.last_name');

        // Apply filters if the Principal uses the dropdown
        if ($request->has('status') && $request->status != 'All') {
            $query->where('leave_applications.status', $request->status);
        } else {
            // Default view: Show Pending at the top, then sort by most recent
            $query->orderByRaw("FIELD(leave_applications.status, 'Pending', 'Approved', 'Denied')")
                ->orderBy('leave_applications.created_at', 'desc');
        }

        $leaves = $query->get();

        // Calculate quick stats for the dashboard
        $stats = [
            'pending' => $leaves->where('status', 'Pending')->count(),
            'approved' => $leaves->where('status', 'Approved')->count(),
            'denied' => $leaves->where('status', 'Denied')->count(),
        ];

        return view('principal.leaves.index', compact('leaves', 'stats'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (Session::get('role_id') != 4) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:Approved,Denied',
            'principal_comment' => 'nullable|string',
        ]);

        DB::table('leave_applications')->where('id', $id)->update([
            'status' => $request->status,
            'principal_comment' => $request->principal_comment,
            'updated_at' => now(),
        ]);

        $message = $request->status == 'Approved' ? 'Leave application approved.' : 'Leave application denied.';

        return back()->with('success', $message);
    }
}
