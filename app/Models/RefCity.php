<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefCity extends Model
{
    protected $table = 'ref_cities';

    protected $fillable = [
        'city_code',
        'city_name',
        'province_code',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(RefProvince::class, 'province_code', 'province_code');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(RefBarangay::class, 'city_code', 'city_code');
    }
}
