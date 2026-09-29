<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AnnouncementTypeController extends Controller
{
    public function index()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $announcementTypes = AnnouncementType::withCount('announcements')->get();

        return view('hr.announcements.settings.index', compact('announcementTypes'));
    }

    public function store(Request $request)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:announcement_types,name',
            'badge_color' => 'nullable|string|max:50',
        ]);

        AnnouncementType::create([
            'name' => $request->name,
            'badge_color' => $request->badge_color ?? 'secondary',
        ]);

        return back()->with('success', 'Announcement category created successfully.');
    }

    public function update(Request $request, $id)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $announcementType = AnnouncementType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:announcement_types,name,'.$announcementType->id,
            'badge_color' => 'nullable|string|max:50',
        ]);

        $announcementType->update([
            'name' => $request->name,
            'badge_color' => $request->badge_color ?? 'secondary',
        ]);

        return back()->with('success', 'Announcement category updated successfully.');
    }

    public function destroy($id)
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $announcementType = AnnouncementType::findOrFail($id);

        if ($announcementType->announcements()->exists()) {
            return back()->with('error', 'Cannot delete this category because it is currently assigned to one or more announcements.');
        }

        $announcementType->delete();

        return back()->with('success', 'Announcement category deleted successfully.');
    }
}
