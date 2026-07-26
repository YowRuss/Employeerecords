<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'content', 'type', 'is_pinned',
        'scheduled_at', 'expires_at', 'created_by',
    ];

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
