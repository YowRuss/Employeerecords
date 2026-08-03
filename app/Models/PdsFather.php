<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsFather extends Model
{
    protected $fillable = [
        'user_id',
        'surname',
        'first_name',
        'middle_name',
        'name_extension',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
