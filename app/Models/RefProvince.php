<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefProvince extends Model
{
    protected $table = 'ref_provinces';

    protected $fillable = [
        'province_code',
        'province_name',
        'region_code',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(RefRegion::class, 'region_code', 'region_code');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(RefCity::class, 'province_code', 'province_code');
    }
}
