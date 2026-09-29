<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = [
        'user_id',
        'office_department',
        'date_of_filing',
        'position',
        'salary',
        'leave_type',
        'leave_type_others',
        'leave_details',
        'leave_details_specific',
        'working_days',
        'inclusive_dates',
        'commutation',
        'status',
        'pay_status',
        'service_credits_used',
        'seminar_credits_used',
        'credits_deducted',
        'hr_remarks',
        'principal_comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
