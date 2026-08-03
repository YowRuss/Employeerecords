<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdsPersonalInfo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pds_personal_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'date_of_birth',
        'place_of_birth',
        'sex',
        'civil_status',
        'height',
        'weight',
        'blood_type',
        'gsis_no',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
        'tin_no',
        'agency_employee_no',
        'residential_address',
        'permanent_address',
        'res_house_no',
        'res_street',
        'res_subdivision',
        'res_region',
        'res_province',
        'res_city',
        'res_barangay',
        'res_zip',
        'res_zipcode',
        'perm_house_no',
        'perm_street',
        'perm_subdivision',
        'perm_region',
        'perm_province',
        'perm_city',
        'perm_barangay',
        'perm_zip',
        'telephone_no',
        'mobile_no',
        'email_address',
        'status',
        'citizenship',
        'citizenship_country_id',
    ];

    /**
     * Get the user that owns the personal information.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to residential region.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(RefRegion::class, 'res_region', 'id');
    }

    /**
     * Relationship to residential province.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(RefProvince::class, 'res_province', 'id');
    }

    /**
     * Relationship to residential city.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(RefCity::class, 'res_city', 'id');
    }

    /**
     * Relationship to residential barangay.
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(RefBarangay::class, 'res_barangay', 'id');
    }

    /**
     * Relationship to permanent region.
     */
    public function permRegion(): BelongsTo
    {
        return $this->belongsTo(RefRegion::class, 'perm_region', 'id');
    }

    /**
     * Relationship to permanent province.
     */
    public function permProvince(): BelongsTo
    {
        return $this->belongsTo(RefProvince::class, 'perm_province', 'id');
    }

    /**
     * Relationship to permanent city.
     */
    public function permCity(): BelongsTo
    {
        return $this->belongsTo(RefCity::class, 'perm_city', 'id');
    }

    /**
     * Relationship to permanent barangay.
     */
    public function permBarangay(): BelongsTo
    {
        return $this->belongsTo(RefBarangay::class, 'perm_barangay', 'id');
    }

    /**
     * Relationship to country for dual citizenship.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'citizenship_country_id', 'id');
    }
}
