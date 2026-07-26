<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'type', 'description', 'event_date',
        'event_time', 'venue', 'max_attendees', 'created_by',
    ];

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }
}
