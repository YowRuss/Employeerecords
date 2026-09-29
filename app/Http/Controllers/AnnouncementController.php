<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAcknowledgment;
use App\Models\AnnouncementType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AnnouncementController extends Controller
{
    // ==========================================
    // HR POWERS: Manage Announcements
    // ==========================================

    public function index()
    {
        // Ensure only HR Admin (Role 2) can access management
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $announcements = Announcement::with('announcementType')->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $announcementTypes = AnnouncementType::all();

        return view('hr.announcements.index', compact('announcements', 'announcementTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'announcement_type_id' => 'required|exists:announcement_types,id',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
            'expires_at' => 'nullable|date|after:scheduled_at',
        ], [
            'scheduled_at.after_or_equal' => 'The scheduled time cannot be in the past.',
            'expires_at.after' => 'The expiration time must be after the scheduled time.',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'announcement_type_id' => $request->announcement_type_id,
            'is_pinned' => $request->has('is_pinned'),
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
            'created_by' => Session::get('user_id'),
        ]);

        return back()->with('success', 'Announcement published successfully!');
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    // View who has acknowledged a specific announcement
    public function tracking($id)
    {
        $announcement = Announcement::with('acknowledgments')->findOrFail($id);

        // Get all active employees (Assuming role_id 1 is Employee)
        $totalEmployees = DB::table('users')->where('role_id', 1)->count();
        $acknowledgedCount = $announcement->acknowledgments->count();

        // Get list of employees who acknowledged
        $acknowledgedUsers = DB::table('announcement_acknowledgments')
            ->join('users', 'announcement_acknowledgments.user_id', '=', 'users.id')
            ->where('announcement_id', $id)
            ->select('users.first_name', 'users.last_name', 'announcement_acknowledgments.acknowledged_at')
            ->get();

        return view('hr.announcements.tracking', compact('announcement', 'totalEmployees', 'acknowledgedCount', 'acknowledgedUsers'));
    }

    // ==========================================
    // EMPLOYEE POWERS: View & Acknowledge
    // ==========================================

    public function acknowledge(Request $request, $id)
    {
        $user_id = Session::get('user_id');

        // Prevent duplicate acknowledgments
        $exists = AnnouncementAcknowledgment::where('announcement_id', $id)
            ->where('user_id', $user_id)
            ->exists();

        if (! $exists) {
            AnnouncementAcknowledgment::create([
                'announcement_id' => $id,
                'user_id' => $user_id,
                'acknowledged_at' => Carbon::now(),
            ]);
        }

        return back()->with('success', 'Announcement acknowledged.');
    }
}
