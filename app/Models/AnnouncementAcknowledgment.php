<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementAcknowledgment extends Model
{
    public $timestamps = false; // We only need acknowledged_at

    protected $fillable = [
        'announcement_id', 'user_id', 'acknowledged_at',
    ];
}
