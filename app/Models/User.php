<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function learningArea()
    {
        return $this->belongsTo(LearningArea::class);
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
}
