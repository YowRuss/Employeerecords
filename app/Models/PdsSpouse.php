<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsSpouse extends Model
{
    protected $fillable = [
        'user_id',
        'surname',
        'first_name',
        'middle_name',
        'name_extension',
        'occupation',
        'employer_business_name',
        'business_address',
        'telephone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
