<?php

namespace App\Models;

use App\Enums\PositionCategory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recovery_email',
        'employee_type',
        'step_increment',
        'last_increment_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'employee_type' => 'integer',
            'last_increment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->position_id && ($user->isDirty('position_id') || $user->employee_type === null)) {
                $position = Position::find($user->position_id);
                if ($position) {
                    $user->employee_type = $position->isTeaching() ? 1 : 0;
                }
            }
        });
    }

    public function isTeaching(): bool
    {
        if ($this->position_id) {
            $position = $this->relationLoaded('position') ? $this->position : Position::find($this->position_id);
            if ($position) {
                return $position->isTeaching();
            }
        }

        return (int) $this->employee_type === 1;
    }

    public function getEmployeeTypeLabelAttribute(): string
    {
        return $this->isTeaching() ? 'TEACHING' : 'NON_TEACHING';
    }

    public function getBaseSalaryAttribute(): float
    {
        if (! $this->position || ! $this->position->salary_grade) {
            return 0.00;
        }

        return (float) SalaryGrade::where('grade', $this->position->salary_grade)
            ->where('step', $this->step_increment ?: 1)
            ->value('amount') ?? 0.00;
    }

    public function syncEmployeeType(): void
    {
        if (! $this->position_id) {
            return;
        }

        $position = Position::find($this->position_id);

        if ($position) {
            $isTeaching = $position->isTeaching();

            if ($isTeaching && $position->category !== PositionCategory::Teaching) {
                $position->category = PositionCategory::Teaching;
                $position->save();
            }

            $this->employee_type = $isTeaching ? 1 : 0;
            $this->saveQuietly();
        }
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function learningArea()
    {
        return $this->belongsTo(LearningArea::class);
    }

    public function pdsPersonalInfo()
    {
        return $this->hasOne(PdsPersonalInfo::class);
    }

    /**
     * PDS sex as Male or Female. Null when the record is missing or unset,
     * so gender-specific leave options stay hidden.
     */
    public function applicantSex(): ?string
    {
        $sex = $this->pdsPersonalInfo?->sex;

        return match (true) {
            in_array($sex, [1, '1', 'Male'], true) => 'Male',
            in_array($sex, [0, '0', 'Female'], true) => 'Female',
            default => null,
        };
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function stepIncrementLogs()
    {
        return $this->hasMany(StepIncrementLog::class);
    }

    /**
     * Original date hired — earliest service record start date.
     * Derived dynamically; no dedicated column needed.
     */
    public function getDateHiredAttribute(): ?string
    {
        if ($this->relationLoaded('serviceRecords')) {
            return $this->serviceRecords->sortBy('date_from')->first()?->date_from;
        }

        return $this->serviceRecords()->orderBy('date_from', 'asc')->value('date_from');
    }

    /**
     * Current employment status — latest service record status.
     * Derived dynamically; no dedicated column needed.
     */
    public function getEmploymentStatusAttribute(): string
    {
        if ($this->relationLoaded('serviceRecords')) {
            return $this->serviceRecords->sortByDesc('date_from')->first()?->status ?? 'Unassigned';
        }

        return $this->serviceRecords()->orderBy('date_from', 'desc')->value('status') ?? 'Unassigned';
    }

    /**
     * Salary amount for the next step increment.
     * Returns null if already at maximum step (8) or no position assigned.
     */
    public function getNextStepRateAttribute(): ?float
    {
        if (! $this->position || ! $this->position->salary_grade || $this->step_increment >= 8) {
            return null;
        }

        return (float) SalaryGrade::where('grade', $this->position->salary_grade)
            ->where('step', $this->step_increment + 1)
            ->value('amount');
    }

    /**
     * Calculate NOSI eligibility date (3 years from last increment or date hired).
     */
    public function getNextEligibilityDateAttribute(): ?string
    {
        $baseDate = $this->last_increment_date ?? $this->date_hired;

        return $baseDate ? Carbon::parse($baseDate)->addYears(3)->format('Y-m-d') : null;
    }

    public function pdsSpouse()
    {
        return $this->hasOne(PdsSpouse::class);
    }

    public function pdsFather()
    {
        return $this->hasOne(PdsFather::class);
    }

    public function pdsMother()
    {
        return $this->hasOne(PdsMother::class);
    }

    public function creditBalance()
    {
        return $this->hasOne(LeaveCreditBalance::class);
    }

    /**
     * Alias with safe defaults — use this in views to avoid null checks.
     *
     * @return HasOne<LeaveCreditBalance, $this>
     */
    public function leaveCreditBalance()
    {
        return $this->hasOne(LeaveCreditBalance::class)->withDefault([
            'vl_balance' => 0.00,
            'sl_balance' => 0.00,
            'service_credits' => 0.00,
        ]);
    }

    public function creditLogs()
    {
        return $this->hasMany(LeaveCreditLog::class);
    }

    public function seminars()
    {
        return $this->hasMany(Seminar::class);
    }

    /**
     * @return HasMany<UserAllowance, $this>
     */
    public function allowances(): HasMany
    {
        return $this->hasMany(UserAllowance::class);
    }

    /**
     * Sum of active PERA allowances for this employee.
     * Returns 2000.00 if assigned and active, or 0.
     */
    public function getActivePeraAttribute(): float
    {
        return (float) $this->allowances()
            ->where('allowance_name', 'PERA')
            ->where('is_active', true)
            ->sum('amount');
    }

    /**
     * @return HasMany<LateDeduction, $this>
     */
    public function lateDeductions(): HasMany
    {
        return $this->hasMany(LateDeduction::class);
    }

    /**
     * @return HasMany<Loan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * @return HasMany<ServiceCredit, $this>
     */
    public function serviceCredits(): HasMany
    {
        return $this->hasMany(ServiceCredit::class);
    }

    /**
     * Earned service-credit days minus days already used to offset absences.
     */
    public function getAvailableCreditsAttribute(): float
    {
        if ($this->relationLoaded('serviceCredits')) {
            $earned = (float) $this->serviceCredits->where('type', 'earned')->sum('days');
            $used = (float) $this->serviceCredits->where('type', 'used')->sum('days');

            return round($earned - $used, 1);
        }

        if (array_key_exists('earned_service_days', $this->attributes)) {
            return round((float) $this->earned_service_days - (float) ($this->used_service_days ?? 0), 1);
        }

        $earned = (float) $this->serviceCredits()->where('type', 'earned')->sum('days');
        $used = (float) $this->serviceCredits()->where('type', 'used')->sum('days');

        return round($earned - $used, 1);
    }

    /**
     * Total monthly amortization withheld for this employee across all
     * active loans that still carry an outstanding balance.
     */
    public function getActiveLoanDeductionsAttribute(): float
    {
        if ($this->relationLoaded('loans')) {
            return (float) $this->loans
                ->where('status', 'Active')
                ->where('running_balance', '>', 0)
                ->sum('monthly_amortization');
        }

        return (float) $this->loans()->outstanding()->sum('monthly_amortization');
    }
}
