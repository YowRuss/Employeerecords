<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventAttendee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EventController extends Controller
{
    // ==========================================
    // HR POWERS: Manage Events
    // ==========================================

    public function index()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        // Fetch events with their assigned advisers
        $events = Event::with('adviser')->orderBy('event_date', 'asc')->orderBy('event_time', 'asc')->get();

        // Fetch employees to populate the Adviser dropdown
        $employees = DB::table('users')
            ->where('role_id', 1)
            ->orderBy('last_name', 'asc')
            ->select('id', 'first_name', 'last_name')
            ->get();

        return view('hr.events.index', compact('events', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'venue' => 'required|string|max:255',
            'max_attendees' => 'nullable|integer|min:1',
            'adviser_id' => 'nullable|exists:users,id',
        ]);

        // Conflict Check: Is this adviser already booked on this date?
        if ($request->adviser_id) {
            $conflict = Event::where('adviser_id', $request->adviser_id)
                ->where('event_date', $request->event_date)
                ->first();

            if ($conflict && ! $request->has('override_conflict')) {
                return back()->with('error', 'Conflict: This employee is already assigned as an adviser for "'.$conflict->title.'" on this date. Choose another adviser or select "Override Conflict" to proceed.')->withInput();
            }
        }

        Event::create([
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue' => $request->venue,
            'max_attendees' => $request->max_attendees,
            'adviser_id' => $request->adviser_id,
            'created_by' => Session::get('user_id'),
        ]);
        // Auto-generate an Announcement if an adviser was assigned
        if ($request->adviser_id) {
            $adviser = DB::table('users')->where('id', $request->adviser_id)->first();
            Announcement::create([
                'title' => 'Official Adviser Assignment: '.$event->title,
                'type' => 'Assignment',
                'content' => 'Attention '.$adviser->first_name.' '.$adviser->last_name.': You have been designated by HR as the official event adviser for '.$event->title.' scheduled on '.Carbon::parse($event->event_date)->format('M d, Y').'.',
                'is_pinned' => 1,
            ]);
        }

        return back()->with('success', 'Event created successfully!');
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();

        return back()->with('success', 'Event deleted.');
    }

    // View attendees and mark attendance
    public function tracking($id)
    {
        $event = Event::with('attendees')->findOrFail($id);

        $attendees = DB::table('event_attendees')
            ->join('users', 'event_attendees.user_id', '=', 'users.id')
            ->where('event_id', $id)
            ->select('event_attendees.*', 'users.first_name', 'users.last_name')
            ->orderBy('users.last_name', 'asc')
            ->get();

        $registeredCount = $attendees->where('status', 'Registered')->count();
        $attendedCount = $attendees->where('status', 'Attended')->count();

        return view('hr.events.tracking', compact('event', 'attendees', 'registeredCount', 'attendedCount'));
    }

    public function markAttendance($id)
    {
        $attendee = EventAttendee::findOrFail($id);

        // Toggle status between Registered and Attended
        $attendee->status = $attendee->status == 'Attended' ? 'Registered' : 'Attended';
        $attendee->save();

        return back()->with('success', 'Attendance updated!');
    }

    // ==========================================
    // EMPLOYEE POWERS: Register for Event
    // ==========================================

    public function register(Request $request, $id)
    {
        $user_id = Session::get('user_id');
        $event = Event::findOrFail($id);

        // Check if event is full
        if ($event->max_attendees) {
            $currentAttendees = EventAttendee::where('event_id', $id)->count();
            if ($currentAttendees >= $event->max_attendees) {
                return back()->with('error', 'Sorry, this event is already full.');
            }
        }

        // Prevent duplicate registration
        $exists = EventAttendee::where('event_id', $id)->where('user_id', $user_id)->exists();

        if (! $exists) {
            EventAttendee::create([
                'event_id' => $id,
                'user_id' => $user_id,
                'status' => 'Registered',
            ]);
        }

        return back()->with('success', 'You have successfully registered for the event!');
    }
}
