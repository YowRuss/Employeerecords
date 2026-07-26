<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefBarangay extends Model
{
    protected $table = 'ref_barangays';

    protected $fillable = [
        'brgy_code',
        'brgy_name',
        'city_code',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(RefCity::class, 'city_code', 'city_code');
    }
}
