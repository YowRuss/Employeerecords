<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HrSettingsController extends Controller
{
    public function positionsAndAreas()
    {
        // Enforce HR access (role 2 or 3 usually, let's assume HR role is 2 or admin is 3)
        if (! in_array(Session::get('role_id'), [2, 3])) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        $positions = Position::orderBy('position_name', 'asc')->get();
        $teachingPositions = Position::where('category', PositionCategory::Teaching->value)->orderBy('position_name', 'asc')->paginate(10, ['*'], 'teaching_page')->withQueryString();
        $nonTeachingPositions = Position::where('category', PositionCategory::NonTeaching->value)->orderBy('position_name', 'asc')->paginate(10, ['*'], 'non_teaching_page')->withQueryString();

        $learningAreas = DB::table('learning_areas')->orderBy('name', 'asc')->paginate(10, ['*'], 'learning_areas_page')->withQueryString();

        return view('hr.settings.positions_areas', compact('positions', 'teachingPositions', 'nonTeachingPositions', 'learningAreas'));
    }

    public function storeLearningArea(Request $request)
    {
        if (! in_array(Session::get('role_id'), [2, 3])) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('learning_areas')->insert([
            'name' => strtoupper($request->name),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hr.settings.positions_areas')->with('success', 'Learning Area added successfully.');
    }

    public function destroyLearningArea($id)
    {
        if (! in_array(Session::get('role_id'), [2, 3])) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        DB::table('learning_areas')->where('id', $id)->delete();

        return redirect()->route('hr.settings.positions_areas')->with('success', 'Learning Area deleted successfully.');
    }

    public function updateLearningArea(Request $request, $id)
    {
        if (! in_array(Session::get('role_id'), [2, 3])) {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::table('learning_areas')->where('id', $id)->update([
            'name' => strtoupper($request->name),
            'updated_at' => now(),
        ]);

        return redirect()->route('hr.settings.positions_areas')->with('success', 'Learning Area updated successfully.');
    }
}
