<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsEducation extends Model
{
    protected $table = 'pds_education';

    protected $fillable = [
        'user_id',
        'level',
        'school_id',
        'degree_course',
        'period_from',
        'period_to',
        'highest_level_earned',
        'year_graduated',
        'scholarship_honors',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }
}
