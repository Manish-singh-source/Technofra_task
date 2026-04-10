<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMarketingLead extends Model
{
    protected $table = 'digital_marketing_leads';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'website',
        'source_page',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}