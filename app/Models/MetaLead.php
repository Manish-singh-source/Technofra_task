<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaLead extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'form_id',
        'page_id',
        'ad_id',
        'full_name',
        'email',
        'phone',
        'city',
        'state',
        'field_data',
        'created_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'field_data' => 'array',
        'created_time' => 'datetime',
    ];
}
