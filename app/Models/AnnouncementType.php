<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementType extends Model
{
    protected $fillable = ['name', 'badge_color'];

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
