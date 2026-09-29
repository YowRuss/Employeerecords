<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'event_type_id', 'description', 'event_date',
        'event_time', 'venue', 'max_attendees', 'adviser_id', 'created_by',
    ];

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function adviser()
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }
}
