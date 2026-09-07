<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'date',
        'date_attended',
        'hours',
        'certificate_path',
        'status',
        'credits_earned',
        'rate_applied',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
