<?php

namespace App\Http\Controllers;

use App\Enums\HolidayType;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    /**
     * Display declared holidays and school calendar dates, newest first.
     */
    public function index()
    {
        $holidays = Holiday::query()
            ->orderByDesc('holiday_date')
            ->orderByDesc('id')
            ->get();

        return view('payroll.holidays.index', [
            'holidays' => $holidays,
            'types' => HolidayType::cases(),
        ]);
    }

    /**
     * JSON events for the monthly holiday calendar.
     */
    public function getCalendarEvents()
    {
        $events = Holiday::query()
            ->orderBy('holiday_date')
            ->get(['id', 'title', 'holiday_date', 'type'])
            ->map(fn (Holiday $holiday): array => [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->holiday_date->toDateString(),
                'color' => $holiday->type->calendarColor(),
                'allDay' => true,
            ])
            ->values();

        return response()->json($events);
    }

    /**
     * Declare a new holiday, suspension, or local school event.
     */
    public function store(Request $request)
    {
        Holiday::create($request->validate($this->rules()));

        return redirect()->route('payroll.holidays.index')
            ->with('success', 'Holiday declared successfully.');
    }

    /**
     * Update an existing holiday entry.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $holiday->update($request->validate($this->rules()));

        return redirect()->route('payroll.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove a holiday from the school calendar.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('payroll.holidays.index')
            ->with('success', 'Holiday removed successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'type' => ['required', 'string', Rule::enum(HolidayType::class)],
        ];
    }
}
