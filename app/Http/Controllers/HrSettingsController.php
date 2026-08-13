<?php

namespace App\Http\Controllers;

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

        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();
        $teachingPositions = DB::table('positions')->where('category', 'Teaching')->orderBy('position_name', 'asc')->get();
        $nonTeachingPositions = DB::table('positions')->where('category', 'Non-Teaching')->orderBy('position_name', 'asc')->get();

        $learningAreas = DB::table('learning_areas')->orderBy('name', 'asc')->get();

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
}
