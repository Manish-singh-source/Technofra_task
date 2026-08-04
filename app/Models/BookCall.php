<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCall extends Model
{
    protected $table = 'bookcall';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'meeting_agenda',
        'meet_link',
        'google_event_id',
        'meet_duration_minutes',
        'booking_date',
        'booking_time',
        'booking_datetime',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i:s',
        'booking_datetime' => 'datetime',
        'meet_duration_minutes' => 'integer',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;
}

