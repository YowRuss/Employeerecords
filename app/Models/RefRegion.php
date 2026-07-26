<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefRegion extends Model
{
    protected $table = 'ref_regions';

    protected $fillable = [
        'region_code',
        'region_name',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(RefProvince::class, 'region_code', 'region_code');
    }
}
