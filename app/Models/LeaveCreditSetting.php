<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCreditSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_type',
        'setting_key',
        'setting_value',
        'description',
    ];
}
