<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCreditBalance extends Model
{
    protected $fillable = [
        'user_id',
        'vl_balance',
        'sl_balance',
        'service_credits',
        'seminar_credits',
        'last_updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
