<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCreditLog extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'leave_bucket',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
