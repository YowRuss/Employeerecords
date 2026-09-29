<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'content', 'announcement_type_id', 'is_pinned',
        'scheduled_at', 'expires_at', 'created_by',
    ];

    public function announcementType()
    {
        return $this->belongsTo(AnnouncementType::class);
    }

    protected $casts = [
        'is_pinned' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function acknowledgments()
    {
        return $this->hasMany(AnnouncementAcknowledgment::class);
    }
}
