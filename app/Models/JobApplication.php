<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'position_id', 'first_name', 'middle_name', 'last_name', 'suffix',
        'email', 'contact_number', 'cover_letter', 'resume_filename', 'resume_data', 'status',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
