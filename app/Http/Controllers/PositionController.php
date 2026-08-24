<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PositionController extends Controller
{
    public function index()
    {
        $teachingPositions = Position::where('category', PositionCategory::Teaching->value)->orderBy('position_name', 'asc')->get();
        $nonTeachingPositions = Position::where('category', PositionCategory::NonTeaching->value)->orderBy('position_name', 'asc')->get();

        return view('hr.positions.index', compact('teachingPositions', 'nonTeachingPositions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'category' => ['required', new Enum(PositionCategory::class)],
        ]);

        Position::create([
            'position_name' => strtoupper($request->position_name),
            'category' => $request->category,
        ]);

        return back()->with('success', 'New position added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'category' => ['nullable', new Enum(PositionCategory::class)],
        ]);

        $position = Position::findOrFail($id);
        $data = [
            'position_name' => strtoupper($request->position_name),
        ];

        if ($request->filled('category')) {
            $data['category'] = $request->category;
        }

        $position->update($data);

        return back()->with('success', 'Position updated successfully!');
    }

    public function destroy($id)
    {
        Position::where('id', $id)->delete();

        return back()->with('success', 'Position deleted.');
    }
}
