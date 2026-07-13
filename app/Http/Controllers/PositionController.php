<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index()
    {
        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();
        return view('hr.positions', compact('positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
        ]);

        DB::table('positions')->insert([
            'position_name' => strtoupper($request->position_name),
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        return back()->with('success', 'New position added successfully!');
    }

    public function destroy($id)
    {
        DB::table('positions')->where('id', $id)->delete();
        return back()->with('success', 'Position deleted.');
    }
}