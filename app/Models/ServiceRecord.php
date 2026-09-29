<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'designation',
        'status',
        'salary',
        'station_place',
        'branch',
        'leave_without_pay',
        'separation_date',
        'separation_cause',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
