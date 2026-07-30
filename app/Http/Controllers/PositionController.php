<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index()
    {
        $teachingPositions = DB::table('positions')->where('category', 'Teaching')->orderBy('position_name', 'asc')->get();
        $nonTeachingPositions = DB::table('positions')->where('category', 'Non-Teaching')->orderBy('position_name', 'asc')->get();

        return view('hr.positions.index', compact('teachingPositions', 'nonTeachingPositions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'category' => 'required|string|in:Teaching,Non-Teaching',
        ]);

        DB::table('positions')->insert([
            'position_name' => strtoupper($request->position_name),
            'category' => $request->category,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'New position added successfully!');
    }

    public function destroy($id)
    {
        DB::table('positions')->where('id', $id)->delete();

        return back()->with('success', 'Position deleted.');
    }
}
