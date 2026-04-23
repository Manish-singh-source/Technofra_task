<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebappLead extends Model
{
    protected $table = 'webapp_leads';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'website',
        'message',
        'source_page',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
