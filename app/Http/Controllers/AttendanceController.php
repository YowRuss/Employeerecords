<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\LateDeduction;
use App\Models\ServiceCredit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class AttendanceController extends Controller
{
    /** Standard working days per month (CSC). */
    private const WORKING_DAYS = 22;

    /** Working hours per day. */
    private const HOURS_PER_DAY = 8;

    /** Minutes per hour. */
    private const MINUTES_PER_HOUR = 60;

    /**
     * Display the attendance & lates management interface.
     */
    public function index(Request $request)
    {
        $periodMonth = (int) $request->input('month', now()->month);
        $periodYear = (int) $request->input('year', now()->year);
        $periodString = sprintf('%04d-%02d', $periodYear, $periodMonth);
        $search = trim((string) $request->input('search', ''));

        $employeeQuery = User::where('role_id', 1)
            ->where('status', 'active')
            ->with(['position'])
            ->withSum(['serviceCredits as earned_service_days' => fn ($query) => $query->where('type', 'earned')], 'days')
            ->withSum(['serviceCredits as used_service_days' => fn ($query) => $query->where('type', 'used')], 'days')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($search !== '') {
            $employeeQuery->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        $employees = $employeeQuery->paginate(5)->withQueryString();

        // Pre-load existing late records for the employees on this page.
        $existingLates = LateDeduction::where('payroll_period', $periodString)
            ->whereIn('user_id', $employees->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $holidays = Holiday::query()
            ->forMonth($periodMonth, $periodYear)
            ->orderBy('holiday_date')
            ->get();

        return view('payroll.attendance.index', [
            'employees' => $employees,
            'existingLates' => $existingLates,
            'holidays' => $holidays,
            'periodMonth' => $periodMonth,
            'periodYear' => $periodYear,
            'periodString' => $periodString,
            'search' => $search,
        ]);
    }

    /**
     * Save or update late deduction records for the selected period.
     */
    public function saveLates(Request $request)
    {
        $request->validate([
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'attendance' => ['required', 'array'],
            'attendance.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance.*.minutes_late' => ['nullable', 'integer', 'min:0'],
            'attendance.*.absent_days' => ['nullable', 'numeric', 'min:0', 'max:22'],
            'attendance.*.dates_absent' => ['nullable'],
            'attendance.*.dates_late' => ['nullable'],
        ]);

        $periodMonth = (int) $request->input('period_month');
        $periodYear = (int) $request->input('period_year');
        $periodString = sprintf('%04d-%02d', $periodYear, $periodMonth);

        $blockedDates = Holiday::query()
            ->forMonth($periodMonth, $periodYear)
            ->get()
            ->mapWithKeys(fn (Holiday $holiday) => [$holiday->holiday_date->toDateString() => $holiday->title])
            ->all();

        $savedCount = 0;
        $creditsApplied = 0.0;
        $periodLabel = now()->setDate($periodYear, $periodMonth, 1)->format('F Y');

        foreach ($request->input('attendance', []) as $entry) {
            $userId = (int) $entry['user_id'];
            $datesLate = $this->normalizeDatesLate(
                $entry['dates_late'] ?? null,
                $periodYear,
                $periodMonth,
                $blockedDates
            );
            $minutesLate = $datesLate !== []
                ? array_sum($datesLate)
                : (int) ($entry['minutes_late'] ?? 0);
            $datesAbsent = $this->normalizeDatesAbsent(
                $entry['dates_absent'] ?? null,
                $periodYear,
                $periodMonth,
                $blockedDates
            );
            $absentDays = $datesAbsent !== []
                ? array_sum($datesAbsent)
                : (float) ($entry['absent_days'] ?? 0);

            // Skip employees with zero minutes and zero absences
            if ($minutesLate <= 0 && $absentDays <= 0) {
                LateDeduction::where('user_id', $userId)
                    ->where('payroll_period', $periodString)
                    ->delete();
                $this->applyServiceCreditOffset($userId, $periodString, $periodLabel, 0);

                continue;
            }

            $user = User::with('position')->find($userId);

            if (! $user) {
                continue;
            }

            $baseSalary = $user->base_salary;
            $dailyRate = $baseSalary / self::WORKING_DAYS;
            $minuteRate = $dailyRate / self::HOURS_PER_DAY / self::MINUTES_PER_HOUR;

            $creditsUsed = $this->applyServiceCreditOffset($userId, $periodString, $periodLabel, $absentDays);
            $billableAbsences = round(max(0, $absentDays - $creditsUsed), 1);
            $creditsApplied += $creditsUsed;

            $lateDeductionAmount = round($minutesLate * $minuteRate, 2);
            $absenceDeductionAmount = round($billableAbsences * $dailyRate, 2);

            LateDeduction::updateOrCreate(
                ['user_id' => $userId, 'payroll_period' => $periodString],
                [
                    'minutes_late' => $minutesLate,
                    'computed_amount' => $lateDeductionAmount,
                    'unexcused_absences' => $absentDays,
                    'dates_absent' => $datesAbsent === [] ? null : $datesAbsent,
                    'dates_late' => $datesLate === [] ? null : $datesLate,
                    'absence_deduction_amount' => $absenceDeductionAmount,
                ]
            );

            $savedCount++;
        }

        $message = "Attendance records for {$periodLabel} saved successfully. {$savedCount} record(s) updated.";
        if ($creditsApplied > 0) {
            $message .= ' '.number_format($creditsApplied, 1).' service credit day(s) offset absences.';
        }

        $redirect = ['month' => $periodMonth, 'year' => $periodYear];
        $page = (int) $request->input('page', 1);
        if ($page > 1) {
            $redirect['page'] = $page;
        }
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $redirect['search'] = $search;
        }

        return redirect()
            ->route('payroll.attendance.index', $redirect)
            ->with('success', $message);
    }

    /**
     * Spend service credits against this period's absences before salary is deducted.
     * A previous offset for the same period is replaced so saving twice does not spend the balance twice.
     */
    private function applyServiceCreditOffset(int $userId, string $periodString, string $periodLabel, float $absentDays): float
    {
        ServiceCredit::query()
            ->where('user_id', $userId)
            ->where('payroll_period', $periodString)
            ->where('type', 'used')
            ->delete();

        if ($absentDays <= 0) {
            return 0;
        }

        $user = User::find($userId);
        if (! $user) {
            return 0;
        }

        $creditsToUse = round(min($absentDays, max(0, (float) $user->available_credits)), 1);
        if ($creditsToUse <= 0) {
            return 0;
        }

        ServiceCredit::create([
            'user_id' => $userId,
            'transaction_date' => now()->toDateString(),
            'description' => 'Auto-offset for '.$periodLabel.' absences',
            'type' => 'used',
            'days' => $creditsToUse,
            'payroll_period' => $periodString,
        ]);

        return $creditsToUse;
    }

    /**
     * Accept a JSON object of Y-m-d => 1|0.5 (or a legacy list of dates) and
     * keep only working days in the selected payroll month.
     *
     * @param  array<string, string>  $blockedDates
     * @return array<string, float>
     */
    private function normalizeDatesAbsent(mixed $raw, int $year, int $month, array $blockedDates): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (! is_array($raw)) {
            return [];
        }

        $entries = [];

        if (array_is_list($raw)) {
            foreach ($raw as $value) {
                if (is_string($value) || is_numeric($value)) {
                    $entries[(string) $value] = 1.0;
                }
            }
        } else {
            foreach ($raw as $date => $weight) {
                $weight = (float) $weight;

                if ($weight === 0.5) {
                    $entries[(string) $date] = 0.5;
                } elseif ($weight >= 1) {
                    $entries[(string) $date] = 1.0;
                }
            }
        }

        $dates = [];

        foreach ($entries as $value => $weight) {
            try {
                $date = Carbon::createFromFormat('!Y-m-d', $value);
            } catch (Throwable) {
                continue;
            }

            if (! $date || $date->format('Y-m-d') !== $value) {
                continue;
            }

            if ((int) $date->year !== $year || (int) $date->month !== $month) {
                continue;
            }

            if ($date->isWeekend() || isset($blockedDates[$value])) {
                continue;
            }

            $dates[$value] = $weight;
        }

        ksort($dates);

        $capped = [];
        $running = 0.0;

        foreach ($dates as $date => $weight) {
            if (($running + $weight) > self::WORKING_DAYS) {
                continue;
            }

            $capped[$date] = $weight;
            $running += $weight;
        }

        return $capped;
    }

    /**
     * Accept a JSON object of Y-m-d => minutes and keep only working days
     * in the selected payroll month.
     *
     * @param  array<string, string>  $blockedDates
     * @return array<string, int>
     */
    private function normalizeDatesLate(mixed $raw, int $year, int $month, array $blockedDates): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (! is_array($raw) || array_is_list($raw)) {
            return [];
        }

        $dates = [];

        foreach ($raw as $value => $minutes) {
            $minutes = (int) $minutes;

            if ($minutes <= 0) {
                continue;
            }

            try {
                $date = Carbon::createFromFormat('!Y-m-d', (string) $value);
            } catch (Throwable) {
                continue;
            }

            if (! $date || $date->format('Y-m-d') !== (string) $value) {
                continue;
            }

            if ((int) $date->year !== $year || (int) $date->month !== $month) {
                continue;
            }

            if ($date->isWeekend() || isset($blockedDates[(string) $value])) {
                continue;
            }

            $dates[(string) $value] = min($minutes, 9999);
        }

        ksort($dates);

        return $dates;
    }
}
