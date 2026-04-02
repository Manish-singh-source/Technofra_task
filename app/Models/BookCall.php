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
        'booking_date',
        'booking_time',
        'booking_datetime',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i:s',
        'booking_datetime' => 'datetime',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;
}
