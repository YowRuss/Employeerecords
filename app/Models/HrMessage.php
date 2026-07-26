<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrMessage extends Model
{
    protected $fillable = [
        'employee_id', 'sender_id', 'message', 'is_read',
    ];

    // The employee whose chat room this belongs to
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // The person who actually typed the message
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
