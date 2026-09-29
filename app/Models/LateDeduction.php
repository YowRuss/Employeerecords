<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class LateDeduction extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'payroll_period',
        'minutes_late',
        'unexcused_absences',
        'dates_absent',
        'dates_late',
        'absence_deduction_amount',
        'computed_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minutes_late' => 'integer',
            'unexcused_absences' => 'decimal:1',
            'dates_absent' => 'array',
            'dates_late' => 'array',
            'absence_deduction_amount' => 'decimal:2',
            'computed_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payroll period as a date (first day of the month).
     */
    public function periodDate(): ?CarbonInterface
    {
        try {
            return Carbon::createFromFormat('!Y-m', (string) $this->payroll_period);
        } catch (Throwable) {
            return null;
        }
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->periodDate()?->format('F Y') ?? (string) $this->payroll_period;
    }

    public function getPeriodMonthAttribute(): int
    {
        return (int) ($this->periodDate()?->month ?? 0);
    }

    public function getPeriodYearAttribute(): int
    {
        return (int) ($this->periodDate()?->year ?? 0);
    }

    /**
     * Date => 1|0.5 map for the employee calendar. Legacy date lists become full days.
     *
     * @return array<string, float>
     */
    public function calendarAbsences(): array
    {
        $raw = $this->dates_absent ?? [];

        if (! is_array($raw)) {
            return [];
        }

        if (array_is_list($raw)) {
            $mapped = [];

            foreach ($raw as $date) {
                if (is_string($date)) {
                    $mapped[$date] = 1.0;
                }
            }

            return $mapped;
        }

        $mapped = [];

        foreach ($raw as $date => $weight) {
            $weight = (float) $weight;

            if (is_string($date) && ($weight === 1.0 || $weight === 0.5)) {
                $mapped[$date] = $weight;
            }
        }

        return $mapped;
    }

    /**
     * Date => minutes map for the employee calendar.
     *
     * @return array<string, int>
     */
    public function calendarLates(): array
    {
        $raw = $this->dates_late ?? [];

        if (! is_array($raw) || array_is_list($raw)) {
            return [];
        }

        $mapped = [];

        foreach ($raw as $date => $minutes) {
            $minutes = (int) $minutes;

            if (is_string($date) && $minutes > 0) {
                $mapped[$date] = $minutes;
            }
        }

        return $mapped;
    }
}
