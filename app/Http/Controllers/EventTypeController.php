<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EventTypeController extends Controller
{
    public function index()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $eventTypes = EventType::withCount('events')->get();

        return view('hr.events.settings.index', compact('eventTypes'));
    }

    public function store(Request $request)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:event_types,name',
            'badge_color' => 'nullable|string|max:50',
        ]);

        EventType::create([
            'name' => $request->name,
            'badge_color' => $request->badge_color ?? 'success',
        ]);

        return back()->with('success', 'Event category created successfully.');
    }

    public function update(Request $request, $id)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $eventType = EventType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:event_types,name,'.$eventType->id,
            'badge_color' => 'nullable|string|max:50',
        ]);

        $eventType->update([
            'name' => $request->name,
            'badge_color' => $request->badge_color ?? 'success',
        ]);

        return back()->with('success', 'Event category updated successfully.');
    }

    public function destroy($id)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $eventType = EventType::findOrFail($id);

        if ($eventType->events()->exists()) {
            return back()->with('error', 'Cannot delete this category because it is currently assigned to one or more events.');
        }

        $eventType->delete();

        return back()->with('success', 'Event category deleted successfully.');
    }
}
