<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $primaryKey = 'school_id';

    protected $fillable = [
        'school_name',
        'school_type',
        'education_level',
        'city',
        'province',
        'region',
    ];
}
