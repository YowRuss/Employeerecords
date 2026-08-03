<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsMother extends Model
{
    protected $fillable = [
        'user_id',
        'maiden_surname',
        'first_name',
        'middle_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
