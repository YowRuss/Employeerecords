<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

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
            'category' => 'required|string|in:0,1,Teaching,Non-Teaching',
            'salary_grade' => 'required|integer|between:1,33',
        ]);

        $cat = $request->category;
        $categoryValue = ($cat === 'Non-Teaching' || $cat === '1' || $cat === 1)
            ? PositionCategory::NonTeaching->value
            : PositionCategory::Teaching->value;

        $position = Position::create([
            'position_name' => strtoupper($request->position_name),
            'category' => $categoryValue,
            'salary_grade' => (int) $request->salary_grade,
        ]);

        $fallbackTab = ($categoryValue === PositionCategory::NonTeaching->value) ? 'non-teaching' : 'teaching';
        $activeTab = $request->input('active_tab', $fallbackTab);

        return back()->with('success', 'New position added successfully!')->with('active_tab', $activeTab);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'category' => 'nullable|string|in:0,1,Teaching,Non-Teaching',
            'salary_grade' => 'nullable|integer|between:1,33',
        ]);

        $position = Position::findOrFail($id);
        $data = [
            'position_name' => strtoupper($request->position_name),
        ];

        if ($request->has('category') && $request->category !== null && $request->category !== '') {
            $cat = $request->category;
            if ($cat === 'Teaching' || $cat === '0' || $cat === 0) {
                $data['category'] = PositionCategory::Teaching->value;
            } elseif ($cat === 'Non-Teaching' || $cat === '1' || $cat === 1) {
                $data['category'] = PositionCategory::NonTeaching->value;
            } else {
                $data['category'] = $cat;
            }
        }

        if ($request->has('salary_grade')) {
            $data['salary_grade'] = ($request->salary_grade !== null && $request->salary_grade !== '')
                ? (int) $request->salary_grade
                : null;
        }

        $position->update($data);

        // Keep employees with this position in sync
        User::where('position_id', $position->id)->each(fn ($u) => $u->syncEmployeeType());

        $fresh = $position->fresh();
        $catVal = $fresh->category instanceof PositionCategory ? $fresh->category->value : (string) $fresh->category;
        $fallbackTab = ($catVal === PositionCategory::NonTeaching->value || $catVal === 'Non-Teaching' || $catVal === '1') ? 'non-teaching' : 'teaching';
        $activeTab = $request->input('active_tab', $fallbackTab);

        return back()->with('success', 'Position updated successfully!')->with('active_tab', $activeTab);
    }

    public function destroy(Request $request, $id)
    {
        $position = Position::find($id);
        $activeTab = 'teaching';

        if ($position) {
            $catVal = $position->category instanceof PositionCategory ? $position->category->value : (string) $position->category;
            $activeTab = ($catVal === PositionCategory::NonTeaching->value || $catVal === 'Non-Teaching' || $catVal === '1') ? 'non-teaching' : 'teaching';
            $position->delete();
        }

        if ($request->filled('active_tab')) {
            $activeTab = $request->input('active_tab');
        }

        return back()->with('success', 'Position deleted.')->with('active_tab', $activeTab);
    }
}
