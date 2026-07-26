<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'position_id', 'department', 'employment_type',
        'location', 'description', 'salary_info', 'is_active',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
